<?php
/**
 * Connector InDesign
 * TreoLabs Premium Module
 * Copyright (c) TreoLabs GmbH
 *
 * This Software is the property of TreoLabs GmbH and is protected
 * by copyright law - it is NOT Freeware and can be used only in one project
 * under a proprietary license, which is delivered along with this program.
 * If not, see <https://treolabs.com/eula>.
 *
 * This Software is distributed as is, with LIMITED WARRANTY AND LIABILITY.
 * Any unauthorised use of this Software without a valid license is
 * a violation of the License Agreement.
 *
 * According to the terms of the license you shall not resell, sublicense,
 * rent, lease, distribute or otherwise transfer rights or usage of this
 * Software or its derivatives. You may modify the code of this Software
 * for your own needs, if source code is provided.
 */

declare(strict_types=1);

namespace ConnectorInDesign\Controllers;

use Espo\Core\Exceptions\BadRequest;

/**
 * Class InDesign
 */
class InDesign extends \Espo\Core\Templates\Controllers\Base
{
    /**
     * @var string
     */
    protected $logDir = 'data/logs/connector-indesign';

    /**
     * Get FileManager
     * @return \Espo\Core\Utils\File\Manager
     */
    protected function getFileManager()
    {
        return $this->getContainer()->get('fileManager');
    }

    /**
     * Log InDesign request
     * @param \Slim\Http\Request $request
     * @param string $typeRequest
     */
    protected function logInDesignRequest($request, $typeRequest)
    {
        if (!$this->getFileManager()->isDir($this->logDir)) {
            $this->getFileManager()->mkdir($this->logDir);
        }

        $logFile = $this->logDir . '/indesign-hook-' . date('Y-m-d') . '.log';

        $data = '[' . date('Y-m-d H:i:s') . '] InDesign (' . $typeRequest . ') Request:';
        $data .= PHP_EOL . 'Request headers: ' . PHP_EOL;
        $data .= $this->getFileManager()->varExport($request->headers());
        $data .= PHP_EOL . 'Request body: ' . PHP_EOL;
        $data .= $this->getFileManager()->varExport($request->getBody());
        $data .= PHP_EOL . PHP_EOL;

        $this->getFileManager()->putContents($logFile, $data, FILE_APPEND);
    }

    /**
     * @param $params
     * @param $data
     * @param \Slim\Http\Request $request
     * @return bool
     * @throws BadRequest
     */
    public function postActionGetProduct($params, $data, $request)
    {
        $this->logInDesignRequest($request, 'POST');
    }

    public function putActionSetProductChanges($params, $data, $request)
    {
        $this->logInDesignRequest($request, 'PUT');
    }
}
