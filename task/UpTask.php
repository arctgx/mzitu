<?php

use lib\TaskBase;

use lib\Log;
use service\Album as AlbumService;

class UpTask extends TaskBase {

    public function doAction() {
        $pn = $this->getParam('pn', 1);

        for ($i=1; $i<=$pn; $i++) {
            AlbumService::getNewestPage($i);
        }
    }
}
