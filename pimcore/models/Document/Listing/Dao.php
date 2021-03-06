<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 * @package    Document
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Model\Document\Listing;

use Pimcore\Model;
use Pimcore\Model\Document;

class Dao extends Model\Listing\Dao\AbstractDao
{

    /** @var  Callback function */
    protected $onCreateQueryCallback;

    /**
     * Loads a list of objects (all are an instance of Document) for the given parameters an return them
     *
     * @return array
     */
    public function load()
    {
        $documents = [];
        $select = (string) $this->getQuery(['id', "type"]);

        $documentsData = $this->db->fetchAll($select, $this->model->getConditionVariables());

        foreach ($documentsData as $documentData) {
            if ($documentData["type"]) {
                if ($doc = Document::getById($documentData["id"])) {
                    $documents[] = $doc;
                }
            }
        }

        $this->model->setDocuments($documents);

        return $documents;
    }

    public function getQuery($columns)
    {
        $select = $this->db->select();
        $select->from(
            [ "documents" ], $columns
        );
        $this->addConditions($select);
        $this->addOrder($select);
        $this->addLimit($select);
        $this->addGroupBy($select);

        if ($this->onCreateQueryCallback) {
            $closure = $this->onCreateQueryCallback;
            $closure($select);
        }

        return $select;
    }

    /**
     * Loads a list of document ids for the specicifies parameters, returns an array of ids
     *
     * @return array
     */
    public function loadIdList()
    {
        $select = (string) $this->getQuery(['id']);
        $documentIds = $this->db->fetchCol($select, $this->model->getConditionVariables());

        return $documentIds;
    }

    public function loadIdPathList()
    {
        $select = (string) $this->getQuery(['id', "CONCAT(path,`key`)"]);
        $documentIds = $this->db->fetchAll($select, $this->model->getConditionVariables());

        return $documentIds;
    }

    public function getCount()
    {
        $select = $this->getQuery([new \Zend_Db_Expr('COUNT(*)')]);
        $amount = (int)$this->db->fetchOne($select, $this->model->getConditionVariables());

        return $amount;
    }

    public function getTotalCount()
    {
        $select = $this->getQuery([new \Zend_Db_Expr('COUNT(*)')]);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select = (string) $select;
        $amount = (int) $this->db->fetchOne($select, $this->model->getConditionVariables());

        return $amount;
    }

    public function onCreateQuery(callable $callback)
    {
        $this->onCreateQueryCallback = $callback;
    }
}
