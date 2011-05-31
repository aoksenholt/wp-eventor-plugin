<?php
/*
 * Base class for all Eventor queries.
 *
 * Handles:
 * - communication against API (through Curl)
 * - caching of complete html result
 *
 * Delegates to subclasses:
 * - setup of query url
 * - html rendering of result xml
 *
 * Main interest method: loadWithCacheKey
 * Design Pattern: Abstract Method
 *
 * Usage:
 *
 * $query = new RealQuery();
 * $query->load();
 * OR $query->loadWithCacheKey($args['widget_id']);
 *
 * $html = $query->getHtml();
 */
abstract class Query
{
	private $xml;
	private $html;
	private $cacheFile;

	protected abstract function getQueryUrl();
	protected abstract function formatHtml($xml);

	// Property getter
	public function getXml()
	{
		return $this->xml;
	}

	protected function setXml($xml)
	{
		$this->xml = $xml;
	}

	// Property getter
	public function getHtml()
	{
		return $this->html;
	}

	protected function setHtml($html)
	{
		$this->html = $html;
	}

	//
	public function load()
	{
		$this->loadWithCacheKey('');
	}

	// Used to cache by widget instance: $queryInstance->loadWithCacheKey($args['widget_id']);
	public function loadWithCacheKey($cacheKey)
	{
		$this->initCache($cacheKey);

		$this->html = $this->cacheLoad();

		if(empty($this->html))
		{
			$this->xml = $this->loadFromEventor();
			$this->html = $this->formatHtml($this->xml);

			$this->cachePut($this->html);
		}
	}

	protected function initCache($cacheKey)
	{
		if (!is_dir(CACHE))
		{
			mkdir(CACHE);
		}
			
		$cacheKey = get_class($this).$cacheKey;
		$this->cacheFile = CACHE . $cacheKey . ".cache";
	}

	protected function loadFromEventor()
	{
		$url = $this->getQueryUrl();
		$xml = $this->getXmlFromUrl($url);

		return $xml;
	}

	protected function cacheLoad()
	{
		if ($this->noCacheFileOrExpired())
		{
			return;
		}

		return file_get_contents($this->cacheFile);
	}

	protected function noCacheFileOrExpired()
	{
		$cache = $this->cacheFile;

		return !file_exists($cache) || (file_exists($cache) && filemtime($cache) < (time() - get_option(MT_EVENTOR_ACTIVITY_TTL)));
	}

	protected function cachePut($html)
	{
		$cachefile = fopen($this->cacheFile, 'wb');
		fwrite($cachefile, $html);
		fclose($cachefile);
	}

	protected function getXmlFromUrl($url)
	{
		$url = get_option(MT_EVENTOR_BASEURL). '/api/' . $url;

		$headers = array('ApiKey' => get_option(MT_EVENTOR_APIKEY));
		
		$response = wp_remote_get($url, array('headers' => $headers, 'sslverify' => false));

		return $response['body'];
	}
}