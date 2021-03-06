<?php

namespace staticsquish;

use Pimple\Container;
use staticsquish\config\Config;
use staticsquish\config\ConfigLoaderIni;
use staticsquish\data\RootDataLoaderIni;
use staticsquish\data\RootDataLoaderTxt;
use staticsquish\models\RootDataObject;
use staticsquish\aggregation\DistinctValuesAggregation;
use staticsquish\filters\RootDataObjectFilter;
use staticsquish\filters\FieldFilter;
use staticsquish\themes\BaseTheme;
use staticsquish\themes\movefast\MoveFastTheme;
use staticsquish\errors\DataErrorTwoRootObjectsHaveSameSlug;
use staticsquish\errors\ConfigErrorNotFound;
use staticsquish\errors\ConfigErrorFieldHasMoreThanOneType;


/**
 *  @license 3-clause BSD
 */
class Site {

	/** @var  Container */
	protected  $app;

	protected $dir;

	/** @var  Config */
	protected $config;

	/** @var BaseTheme **/
	protected $theme;

	function __construct(Container $app, $dir)
	{
		$this->app = $app;
		$this->dir = $dir;
		$this->config = new Config();

        $anyConfigFound = false;

		foreach(array(
			New ConfigLoaderIni(),
				) as $loader) {
			if ($loader->isLoadableConfigInSite($this)) {
				$loader->loadConfigInSite($this->config, $this);
                $anyConfigFound = true;
			}
		}

        if (!$anyConfigFound) {
            $this->errors[] = new ConfigErrorNotFound();
        } else {
            // check config

            foreach($this->config->fields as $field) {
                if ($field->isMoreThanOneType()) {
                    $this->errors[] = new ConfigErrorFieldHasMoreThanOneType();
                }
            }


        }


		$this->theme = new MoveFastTheme($this->app);

  }

	protected $isLoaded = false;

	protected $errors = array();
	protected $warnings = array();

	protected $rootDataObjects = array();

	function load() {

        if ($this->isLoaded || $this->errors) {
            return;
        }

		$loaders = array(
			new RootDataLoaderIni(),
			new RootDataLoaderTxt(),
		);

		foreach(scandir($this->dir . DIRECTORY_SEPARATOR. "data") as $fileName) {
			if ($fileName != "." && $fileName != '..' && is_dir($this->dir . DIRECTORY_SEPARATOR. "data".DIRECTORY_SEPARATOR.$fileName)) {

				foreach($loaders as $loader) {
					if ($loader->isLoadableDataInSite($this, $fileName)) {
						$out = $loader->loadRootDataInSite($this, $fileName);
                        foreach ($out->getRootDataObjects() as $rootDataObject) {
                            $this->addRootDataObject($rootDataObject);
                        }
                        foreach($out->getErrors() as $error) {
                            $this->errors[] = $error;
                        }
                        foreach($out->getWarnings() as $warning) {
                            $this->warnings[] = $warning;
                        }
					}
				}

			}
		}

		$this->isLoaded = true;

	}

	protected function addRootDataObject(RootDataObject $rootDataObject) {
		foreach($this->rootDataObjects as $existingRootDataObject) {
			if ($existingRootDataObject->getSlug() == $rootDataObject->getSlug()) {
				$this->errors[] = new DataErrorTwoRootObjectsHaveSameSlug();
			}
		}
		$this->rootDataObjects[] = $rootDataObject;
	}


	function write($outDir) {

		if (!$this->isLoaded) {
			$this->load();
		}

		$this->theme->write($this, $outDir);

	}



	/**
	 * @return String
	 */
	public function getDir()
	{
		return $this->dir;
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @return boolean
	 */
	public function isIsLoaded()
	{
		return $this->isLoaded;
	}

	/**
	 * @return array
	 */
	public function geterrors()
	{
		if (!$this->isLoaded) {
			$this->load();
		}
		return $this->errors;
	}

	/**
	 * @return array
	 */
	public function getWarnings()
	{
		if (!$this->isLoaded) {
			$this->load();
		}
		return $this->warnings;
	}


  /**
   * Get the value of Root Data Objects
   *
   * @return mixed
   */
  public function getRootDataObjects()
  {
      return $this->rootDataObjects;
  }

}
