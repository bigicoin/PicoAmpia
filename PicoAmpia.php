<?php

/**
 * Pico Ampia - a plugin to support Google AMP and Facebook Instant Articles
 *
 * Google AMP (Accelerated Mobile Pages) and Facebook Instant Articles (IA) are
 * both formatted pages that are slightly different from just regular HTML web pages.
 * The Ampia plugin supports AMP pages and IA pages, when used in conjunction with
 * theme files accordingly.
 *
 * @author  Bigi Lui
 * @link    http://picocms.org
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 1.0
 */
final class PicoAmpia extends AbstractPicoPlugin
{
	/**
	 * This plugin is enabled by default?
	 *
	 * @see AbstractPicoPlugin::$enabled
	 * @var boolean
	 */
	protected $enabled = false;

	/**
	 * This plugin depends on ...
	 *
	 * @see AbstractPicoPlugin::$dependsOn
	 * @var string[]
	 */
	protected $dependsOn = array();

	/**
	 * Default URL roots for AMP and IA.
	 */
	protected $urlRoots = array('amp' => 'amp', 'ia' => 'ia');

	/**
	 * Flag for if this current request is AMP or IA or just normal.
	 */
	protected $ampiaMode = null;

	/**
	 * Cache some values
	 */
	protected $contentDir = '';
	protected $contentExt = '';
	protected $baseUrl = '';
	protected $siteTitle = '';
	protected $requestUrl = '';
	protected $facebookPageId = '';
	protected $facebookDefaultStyle = '';
	protected $facebookRssUrl = '';

	/**
	 * Triggered after Pico has read its configuration
	 *
	 * @see    Pico::getConfig()
	 * @param  array &$config array of config variables
	 * @return void
	 */
	public function onConfigLoaded(array &$config)
	{
		// load configured url roots if they exist
		if (isset($config['PicoAmpia.ampRoot'])) {
			$this->urlRoots['amp'] = $config['PicoAmpia.ampRoot'];
		}
		if (isset($config['PicoAmpia.iaRoot'])) {
			$this->urlRoots['ia'] = $config['PicoAmpia.iaRoot'];
		}
		if (!empty($config['PicoAmpia.facebookPageId'])) {
			$this->facebookPageId = $config['PicoAmpia.facebookPageId'];
		}
		if (!empty($config['PicoAmpia.facebookDefaultStyle'])) {
			$this->facebookDefaultStyle = $config['PicoAmpia.facebookDefaultStyle'];
		}
		if (!empty($config['PicoAmpia.iaRss'])) {
			$this->facebookRssUrl = $config['PicoAmpia.iaRss'];
		}
		// cache some values from config
		$this->baseUrl = $config['base_url'];
		$this->siteTitle = $config['site_title'];
		$this->contentDir = $config['content_dir'];
		$this->contentExt = $config['content_ext'];
	}

	/**
	 * Triggered after Pico has evaluated the request URL
	 *
	 * @see    Pico::getRequestUrl()
	 * @param  string &$url part of the URL describing the requested contents
	 * @return void
	 */
	public function onRequestUrl(&$url)
	{
		if (!empty($this->facebookRssUrl) && $url == $this->facebookRssUrl) {
			// facebook instant articles rss feed
			$this->renderRss();
			exit; // exit the process, do not go ahead with Pico processing.
		}

		$parts = explode('/', $url);
		if (!empty($this->urlRoots['amp']) && $parts[0] == $this->urlRoots['amp']) {
			$this->ampiaMode = 'amp';
		} else if (!empty($this->urlRoots['ia']) && $parts[0] == $this->urlRoots['ia']) {
			$this->ampiaMode = 'ia';
		}

		if (!empty($this->ampiaMode)) {
			// Ampia mode is amp or ia, override the requested url.
			array_shift($parts);
			$url = implode('/', $parts);
		}

		// cache some values
		$this->requestUrl = $url;
	}

	/**
	 * Triggered when Pico reads its known meta header fields
	 *
	 * @see    Pico::getMetaHeaders()
	 * @param  string[] &$headers list of known meta header
	 *     fields; the array value specifies the YAML key to search for, the
	 *     array key is later used to access the found value
	 * @return void
	 */
	public function onMetaHeaders(array &$headers)
	{
		$headers['iastylename'] = 'IA-Style-Name';
		$headers['iasubtitle'] = 'IA-Subtitle';
		$headers['iakicker'] = 'IA-Kicker';
		$headers['iacoverimage'] = 'IA-Cover-Image';
	}

	/**
	* Triggered after Pico has parsed the meta header
	*
	* @see    Pico::getFileMeta()
	* @param  string[] &$meta parsed meta data
	* @return void
	*/
	public function onMetaParsed(array &$meta)
	{
		if (empty($meta['iastylename'])) {
			if (!empty($this->facebookDefaultStyle)) {
				$meta['iastylename'] = $this->facebookDefaultStyle;
			}
		}
		// ensure the existence of author for Facebook IA, which is needed.
		if (!empty($meta['author'])) {
			$meta['iaauthor'] = $meta['author'];
		} else {
			$meta['iaauthor'] = 'Unknown Author';
		}
	}

	/**
	 * Triggered before Pico renders the page
	 *
	 * @see    Pico::getTwig()
	 * @see    DummyPlugin::onPageRendered()
	 * @param  Twig_Environment &$twig          twig template engine
	 * @param  array            &$twigVariables template variables
	 * @param  string           &$templateName  file name of the template
	 * @return void
	 */
	public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
	{
		if (!empty($this->ampiaMode)) {
			// we don't check for existence of the files, because we don't have access to the themesDir variable.
			// when using this plugin, the webmaster should ensure they have the proper theme files.
			$templateName = $this->urlRoots[$this->ampiaMode] . '/' . $templateName;
		}
	}

	/**
	 * Triggered after Pico has rendered the page
	 *
	 * @param  string &$output contents which will be sent to the user
	 * @return void
	 */
	public function onPageRendered(&$output)
	{
		if ($this->ampiaMode == 'amp') {
			// AMP tags to replace
			str_replace('<img', '<amp-img', $output);
			str_replace('</img', '</amp-img', $output);
		} else if ($this->ampiaMode == 'ia') {
			// IA tags
		} else {
			// regular pages
			$output = str_replace('</head>', ($this->buildExtraHeaders() . '</head>'), $output);
		}
	}

	/**
	 * Add some extra header tags to regular html pages for AMP and IA info.
	 */
	private function buildExtraHeaders() {
		$headers = '';
		if (!empty($this->urlRoots['amp'])) {
			$headers .= PHP_EOL.'<link rel="amphtml" href="'.$this->baseUrl.$this->urlRoots['amp'].'/'.$this->requestUrl.'">';
		}
		if (!empty($this->urlRoots['ia'])) {
			$headers .= PHP_EOL.'<meta property="fb:pages" content="'.$this->facebookPageId.'" />';
		}
		return $headers;
	}

	/**
	 * Render Facebook Instant Articles RSS feed
	 */
	private function renderRss() {
		$lastHour = time() - 3600; // FB wants all pages updated within the last hour
		$files = $this->getFiles($this->contentDir, $this->contentExt, $lastHour); // get all files last modified recently
		// now get to the proper urls from them
		$spliceLength = strlen($this->contentDir);
		foreach ($files as $i => $file) {
			// get urls out of full paths
			$files[$i] = substr($file, $spliceLength);
			$parts = explode('/', $files[$i]);
			if ($parts[count($parts)-1] == 'index') {
				$parts[count($parts)-1] = ''; // if index, use the blank one as url
			}
			$files[$i] = implode('/', $parts);
		}
		header('Content-type: application/xml');
		echo '<rss version="2.0"
xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <channel>
    <title>'.$this->siteTitle.'</title>
    <link>'.$this->baseUrl.'</link>
    <description>
      '.$this->siteTitle.'
    </description>
    <language>en-us</language>
    <lastBuildDate>'.date('r').'</lastBuildDate>';
    	echo "\n";
    	foreach ($files as $file) {
    		echo "<item>\n";
    		echo "<title>".$this->baseUrl.$file."</title>\n";
    		echo "<link>".$this->baseUrl.$file."</link>\n";
    		echo "<content:encoded>\n<![CDATA[\n".file_get_contents($this->baseUrl.$this->urlRoots['ia'].'/'.$file)."\n]]>\n</content:encoded>\n";
    		echo "</item>\n";
    	}
    	echo "\n";
    	echo '  </channel>
</rss>';
	}

	/**
	 * Get all files in content dir
	 */
	private function getFiles($directory, $fileExtension, $timeCutOff)
	{
		$directory = rtrim($directory, '/');
		$result = array();

		// scandir() reads files in alphabetical order
		$files = scandir($directory);
		$fileExtensionLength = strlen($fileExtension);
		if ($files !== false) {
			foreach ($files as $file) {
				// exclude hidden files/dirs starting with a .; this also excludes the special dirs . and ..
				// exclude files ending with a ~ (vim/nano backup) or # (emacs backup)
				if ((substr($file, 0, 1) === '.') || in_array(substr($file, -1), array('~', '#'))) {
					continue;
				}

				if (is_dir($directory . '/' . $file)) {
					// get files recursively
					$result = array_merge($result, $this->getFiles($directory . '/' . $file, $fileExtension, $timeCutOff));
				} elseif (empty($fileExtension) || (substr($file, -$fileExtensionLength) === $fileExtension)) {
					if (filemtime($directory . '/' . $file) > $timeCutOff) {
						$fileWithoutExt = substr($file, 0, -$fileExtensionLength);
						$result[] = $directory . '/' . $fileWithoutExt;
					}
				}
			}
		}

		return $result;
	}
}
