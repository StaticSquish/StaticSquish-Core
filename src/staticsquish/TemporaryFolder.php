<?php

namespace staticsquish;


/**
*  @license 3-clause BSD
*/
class TemporaryFolder
{

  protected $folder;

  public function get() {
    if (!$this->folder) {
      $this->folder = '/tmp/staticsquish'.rand();
      while(file_exists($this->folder)) {
        $this->folder = '/tmp/staticsquish'.rand();
      }
      mkdir($this->folder);
    }
    return $this->folder;
  }

}
