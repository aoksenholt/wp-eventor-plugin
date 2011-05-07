<?php
/*
 * Base class for all Eventor queries.
 * Handles 
 * - communication against API (through Curl)
 * - caching
 * 
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
	
    // Property getter		
	public function getHtml()
	{		
		return $this->html;				
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
	
	private function initCache($cacheKey)
	{
		if (!is_dir(CACHE))
		{
			mkdir(CACHE);
		}
			
		$cacheKey = get_class($this).$cacheKey;
		$this->cacheFile = CACHE . $cacheKey . ".cache";
	}
	
	private function loadFromEventor()
	{
		$url = $this->getQueryUrl();
		$xml = $this->getXmlFromUrl($url);
	
		return $xml;
	}

	
	private function cacheLoad()
	{			
		if ($this->noCacheFileOrExpired()) 
		{			
			return;
		}
		
		return file_get_contents($this->cacheFile);
	}
	
	private function noCacheFileOrExpired()
	{
		$cache = $this->cacheFile;
		
		return !file_exists($cache) || (file_exists($cache) && filemtime($cache) < (time() - get_option(MT_EVENTOR_ACTIVITY_TTL)));
	}
	
	private function cachePut($html)
	{
		$cachefile = fopen($this->cacheFile, 'wb');
		fwrite($cachefile, $html);
		fclose($cachefile);
	}
	
	private function getXmlFromUrl($url)
	{
		$url = get_option(MT_EVENTOR_BASEURL). '/api/' . $url;
				
		// create curl resource
		$ch = curl_init();
		// set url
		curl_setopt($ch, CURLOPT_URL, $url);
		// return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		// set header
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("ApiKey: " . get_option(MT_EVENTOR_APIKEY)));
	
		// $output contains the output string
		$output = curl_exec($ch);
	
		if (!$output)
			echo curl_error($ch);
	
		// close curl resource to free up system resources
		curl_close($ch);
	
		return $output;
	}
}