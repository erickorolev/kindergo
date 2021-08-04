<?php

class Vtiger_ImageReceiver_Service
{
    public const AVATAR_FIELD = 'image_avatar';

    public const DOCUMENTS_FIELD = 'images_list';

    protected string $column;

    protected ?string $value;

    protected int $record_id;

    public function __construct(int $record_id, string $column, ?string $value)
    {
        $this->record_id = $record_id;
        $this->column = $column;
        $this->value = $value;
    }

    public function getAvatar(): array
    {
        if ($this->record_id < 1) {
            return [];
        }
        /** @var Contacts_Record_Model $recordModel */
        $recordModel = Vtiger_Record_Model::getInstanceById($this->record_id);
        if (!$recordModel) {
            return [];
        }
        return $recordModel->getImageDetails();
    }

    public function getImages(): array
    {
        if ($this->record_id < 1) {
            return [];
        }
        /** @var Vtiger_Record_Model $recordModel */
        $recordModel = Vtiger_Record_Model::getInstanceById($this->record_id);
        if (!$recordModel) {
            return [];
        }
        return $recordModel->getFileDetails();
    }
}