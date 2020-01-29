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

use Espo\Core\Exceptions\Forbidden;

/**
 * Class InDesign
 *
 * @author o.trelin <o.trelin@treolabs.com>
 * @author d.talko <d.talko@treolabs.com>
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

        $logFile = $this->logDir . '/indesign-request-' . date('Y-m-d') . '.log';

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
     * @return array
     */
    public function postActionGetProduct($params, $data, $request)
    {
        $this->logInDesignRequest($request, 'POST');

        $body = (array)json_decode($request->getBody(), true);

        if (!empty($body['ids'])) {
            $where = [
                'id' => $body['ids']
            ];
        } elseif (!empty($body['where'])) {
            $where = [
                $body['where']
            ];
        } else {
            $where = [
                'isActive' => true
            ];
        }

        $return = [];
        $productsEntity = $this->getEntityManager()->getRepository('Product')->where($where)->find();

        foreach ($productsEntity as $key => $productEntity) {
            if ($this->getAcl()->checkEntity($productEntity, 'read')) {
                $return['records'][$key] = [
                    'id' => $productEntity->get('id'),
                    'fields' => [
                        'name' => $productEntity->get('name'),
                        'sku' => $productEntity->get('sku'),
                        'type' => $productEntity->get('type'),
                        'amount' => $productEntity->get('amount'),
                        'finalPrice' => $productEntity->get('finalPrice'),
                        'productStatus' => $productEntity->get('productStatus'),
                        'ean' => $productEntity->get('ean'),
                        'mpn' => $productEntity->get('mpn'),
                        'uvp' => $productEntity->get('uvp'),
                        'longDescription' => $productEntity->get('longDescription'),
                        'priceCurrency' => $productEntity->get('priceCurrency'),
                        'brandName' => $productEntity->get('brandName'),
                        'taxName' => $productEntity->get('taxName')
                    ]
                ];

                if (!empty($productEntity->get('productFamilyName'))) {
                    $return['records'][$key]['fields']['productFamilyName'] = $productEntity->get('productFamilyName');
                }
                if (!empty($productEntity->get('catalogName'))) {
                    $return['records'][$key]['fields']['catalogName'] = $productEntity->get('catalogName');
                }
            } else {
                throw new Forbidden ("This user can't read products");
            }
        }

        return $return;
    }

    /**
     * @param $params
     * @param $data
     * @param \Slim\Http\Request $request
     * @return string
     */
    public function putActionSetProductChanges($params, $data, $request)
    {
        $this->logInDesignRequest($request, 'PUT');

        $body = (array)json_decode($request->getBody(), true);

        foreach ($body['records'] as $productInDesign) {
            $productEntity = $this->getEntityManager()->getRepository('Product')->where([
                'id' => $productInDesign['id']
            ])->findOne();
            if ($this->getAcl()->checkEntity($productEntity, 'edit')) {
                $save = false;
                foreach ($productInDesign['fields'] as $field => $value) {
                    if ($value != $productEntity->get($field)) {
                        $save = true;
                        $productEntity->set([
                            $field => $value
                        ]);
                    }
                }
                if ($save) {
                    $this->getEntityManager()->saveEntity($productEntity);
                }
            } else {
                throw new Forbidden ("This user can't change products");
            }
        }

        return 'Success';
    }
}
