<?php

namespace Application\Bundle\FrontBundle\Components;

use Symfony\Component\DependencyInjection\ContainerAware;
use Application\Bundle\FrontBundle\Helper\ExportFields;
use Application\Bundle\FrontBundle\SphinxSearch\SphinxSearch;
use PHPExcel_Cell;

class ExportReport extends ContainerAware
{

    public $columns;
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function prepareManifestReport($activeSheet, $records)
    {
        $row = 1;
        $columns = new ExportFields();
        $this->columns = $columns->getManifestColumns();
        foreach ($this->columns as $column => $columnName) {
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $row, $columnName);
            $activeSheet->getColumnDimensionByColumn($column)->setWidth(20);
            $activeSheet->getStyleByColumnAndRow($column)->getAlignment()->setWrapText(true);
            $activeSheet->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
        }
        $activeSheet->getRowDimension($row)->setRowHeight(50);
        $row ++;

        foreach ($records as $record) {

            $activeSheet->setCellValueExplicitByColumnAndRow(0, $row, $record->getUniqueId());
            $activeSheet->setCellValueExplicitByColumnAndRow(1, $row, ($record->getUser()->getOrganizations()) ? $record->getUser()->getOrganizations()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(2, $row, $record->getCollectionName());
            $activeSheet->setCellValueExplicitByColumnAndRow(3, $row, ($record->getFormat()->getName()) ? $record->getFormat()->getName() : '');
            $printType = '';
            if ($record->getFilmRecord()) {
                $printType = ($record->getFilmRecord()->getPrintType()) ? $record->getFilmRecord()->getPrintType()->getName() : '';
            }
            $activeSheet->setCellValueExplicitByColumnAndRow(4, $row, $printType);

            $mediaType = ($record->getReelDiameters()) ? $record->getReelDiameters()->getName() . "\n" : '';
            if ($record->getAudioRecord()) {
                $mediaType .=($record->getAudioRecord()->getDiskDiameters()) ? $record->getAudioRecord()->getDiskDiameters()->getName() . "\n" : '';
            }
            if ($record->getVideoRecord()) {
                $mediaType .=($record->getVideoRecord()->getCassetteSize()) ? $record->getVideoRecord()->getCassetteSize()->getName() . "\n" : '';
            }
            $activeSheet->setCellValueExplicitByColumnAndRow(5, $row, $mediaType);
            $activeSheet->getStyleByColumnAndRow(5, $row)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValueExplicitByColumnAndRow(6, $row, $record->getTitle());
            $duration = $record->getContentDuration();
            if (empty($duration) || $duration < 0) {
                if ($record->getAudioRecord()) {
                    $duration = ($record->getAudioRecord()->getMediaDuration()) ? $record->getAudioRecord()->getMediaDuration() : '';
                }
            }
            $activeSheet->setCellValueExplicitByColumnAndRow(7, $row, $duration);
            $row ++;
        }
    }

    public function generateReport($records)
    {
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("AVCC - AVPreserve")
                ->setTitle("AVCC - Report")
                ->setSubject("Report for all formats")
                ->setDescription("Report for all formats");
        $activeSheet = $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle('All Formats');
        $row = 1;
// Prepare header row for report
        $this->prepareHeader($activeSheet, $row);
        $row ++;
        $this->prepareRecords($activeSheet, $row, $records);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        return $phpExcelObject;
    }

    public function outputReport($type, $phpExcelObject, $fileStartName = 'allFormat')
    {
        $format = ($type == 'csv') ? 'CSV' : 'Excel2007';
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, $format);
        $filename = $fileStartName . '_' . time() . '.' . $type;
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment;filename={$filename}");
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    /**
     * Save report on server.
     * @param  type   $type
     * @param  type   $phpExcelObject
     * @return string
     */
    public function saveReport($type, $phpExcelObject)
    {
        $format = ($type == 'csv') ? 'CSV' : 'Excel2007';
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, $format);
        $filename = 'allFormat_' . time() . '.' . $type;
        $folderPath = $this->container->getParameter('webUrl') . 'exports/' . date('Y') . '/' . date('m') . '/';
        $completePath = $folderPath . $filename;
        $downloadPath = 'exports/' . date('Y') . '/' . date('m') . '/' . $filename;
        if (!is_dir($folderPath))
            mkdir($folderPath, 0777, TRUE);

        $writer->save($completePath);

        return $downloadPath;
    }

    /**
     * Create the Header for report.
     *
     * @param  PHPExcel_Worksheet $activeSheet
     * @param  Integer            $row
     * @return boolean
     */
    private function prepareHeader($activeSheet, $row)
    {
        $columns = new ExportFields();
        $this->columns = $columns->getExportColumns();
        foreach ($this->columns as $column => $columnName) {
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $row, str_replace('_', ' ', $columnName));
            $activeSheet->getColumnDimensionByColumn($column)->setWidth(20);
            $activeSheet->getStyleByColumnAndRow($column)->getFont()->setBold(true);
        }

        return TRUE;
    }

    /**
     * Prepare rows for records.
     *
     * @param  PHPExcel_Worksheet $activeSheet
     * @param  Integer            $row
     * @return boolean
     */
    private function prepareRecords($activeSheet, $row, $records)
    {

        foreach ($records as $record) {
            $this->makeExcelRows($activeSheet, $record, $row);
            $row ++;
        }

        return true;
    }

    public function initReport()
    {
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("AVCC - AVPreserve")
                ->setTitle("AVCC - Report")
                ->setSubject("Report for all formats")
                ->setDescription("Report for all formats");
        $activeSheet = $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle('All Formats');
        $row = 1;
// Prepare header row for report
        $this->prepareHeader($activeSheet, $row);

        return $phpExcelObject;
    }

    /**
     * Get records from sphinx
     *
     * @param type $user
     * @param type $sphinxInfo
     * @param type $sphinxCriteria
     * @param type $em
     *
     * @return type
     */
    public function fetchFromSphinx($user, $sphinxInfo, $sphinxCriteria, $em)
    {
        $phpExcelObject = $this->initReport();
        $row = 2;
        $count = 0;
        $offset = 0;
        $sphinxObj = new SphinxSearch($em, $sphinxInfo);
        $activeSheet = $phpExcelObject->setActiveSheetIndex(0);
        while ($count == 0) {
            $records = $sphinxObj->select($user, $offset, 1000, 'title', 'asc', $sphinxCriteria);
            $rec = $records[0];
            $totalFound = $records[1][1]['Value'];
            $this->prepareRecordsFromSphinx($activeSheet, $row, $rec);
            $offset = $offset + 1000;
            $row++;
            if ($totalFound < 1000) {
                $count++;
            }
        }
        $phpExcelObject->setActiveSheetIndex(0);

        return $phpExcelObject;
    }

    /**
     * Prepare rows for records.
     *
     * @param  PHPExcel_Worksheet $activeSheet
     * @param  Integer            $row
     * @return boolean
     */
    private function prepareRecordsFromSphinx($activeSheet, $row, $records)
    {
        foreach ($records as $record) {
            $this->makeExcelRowsByArray($activeSheet, $record, $row);
            $row ++;
        }
    }

    public function megerRecords($records, $mergeToFile)
    {
        $mergeFileCompletePath = $this->container->getParameter('webUrl') . 'merge/' . date('Y') . '/' . date('m') . '/' . $mergeToFile;
        if (file_exists($mergeFileCompletePath)) {
            $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject($mergeFileCompletePath);
            $newphpExcelObject = $this->initReport();
            $activeSheet = $newphpExcelObject->setActiveSheetIndex(0);

            foreach ($phpExcelObject->getWorksheetIterator() as $worksheet) {
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $excelCell = new PHPExcel_Cell(null, null, $worksheet);
                $highestColumnIndex = $excelCell->columnIndexFromString($highestColumn);
                if ($highestRow > 0) {
                    $rows = array();
                    $newRows = array();
                    $newrow = 2;
                    $matched = false;
                    $uniqueIdIndex = 3;
                    for ($row = 1; $row <= 1; ++$row) {
                        for ($col = 0; $col < $highestColumnIndex; ++$col) {
                            $cell = $worksheet->getCellByColumnAndRow($col, $row);
                            if (strtolower(str_replace('_', ' ', $cell->getValue())) == 'unique id') {
                                $uniqueIdIndex = $col;
                            }
                        }
                    }
                    for ($row = 2; $row <= $highestRow; ++$row) {
                        for ($col = 0; $col < $highestColumnIndex; ++$col) {
                            $uniq = strtolower(str_replace(' ', '_', $worksheet->getCellByColumnAndRow($uniqueIdIndex, $row)));
                            $cell = $worksheet->getCellByColumnAndRow($col, $row);
                            $columnName = strtolower(str_replace(' ', '_', $worksheet->getCellByColumnAndRow($col, 1)));
                            $rows[$uniq][$columnName] = $cell->getValue();
                        }
                    }
                    foreach ($records as $rec) {
                        $recUniq = strtolower(str_replace(' ', '_', $rec->getUniqueId()));
                        if (array_key_exists($recUniq, $rows)) {
//                            $newRows = $this->appendCellValuesByObject($rec, $rows[$recUniq]);
                            $this->makeExcelRowsByArray($activeSheet, $rec, $rows[$recUniq], $newrow);
                            unset($rows[$recUniq]);
                        } else {
                            $this->makeExcelRows($activeSheet, $rec, $newrow);
                        }
                        $newrow++;
                    }
                    if (count($rows) > 0) {
                        foreach ($rows as $row) {
                            $this->makeExcelRowsByArray($activeSheet, false, $row, $newrow);
                            $newrow++;
                        }
                    }

                    return $newphpExcelObject;
                } else {
                    return "The file $mergeToFile is empty";
                }
            }
        } else {
            return "The file $mergeToFile does not exist";
        }
    }

    public function makeExcelRows($activeSheet, $record, $row)
    {
        $activeSheet->setCellValueExplicitByColumnAndRow(0, $row, $record->getProject());
        $activeSheet->setCellValueExplicitByColumnAndRow(1, $row, $record->getCollectionName());
        $activeSheet->setCellValueExplicitByColumnAndRow(2, $row, $record->getMediaType());
        $activeSheet->setCellValueExplicitByColumnAndRow(3, $row, $record->getUniqueId());
        $activeSheet->setCellValueExplicitByColumnAndRow(4, $row, $record->getLocation());
        $activeSheet->setCellValueExplicitByColumnAndRow(5, $row, ($record->getFormat()->getName()) ? $record->getFormat()->getName() : '');
        $activeSheet->setCellValueExplicitByColumnAndRow(6, $row, $record->getTitle());
        $activeSheet->setCellValueExplicitByColumnAndRow(7, $row, $record->getDescription());
        $activeSheet->setCellValueExplicitByColumnAndRow(8, $row, ($record->getCommercial()) ? $record->getCommercial()->getName() : '');
        $activeSheet->setCellValueExplicitByColumnAndRow(9, $row, $record->getContentDuration());
        $activeSheet->setCellValueExplicitByColumnAndRow(11, $row, $record->getCreationDate());
        $activeSheet->setCellValueExplicitByColumnAndRow(12, $row, $record->getContentDate());
        $activeSheet->setCellValueExplicitByColumnAndRow(16, $row, ($record->getReelDiameters()) ? $record->getReelDiameters()->getName() : '');
        $activeSheet->setCellValueExplicitByColumnAndRow(34, $row, ($record->getGenreTerms()));
        $activeSheet->setCellValueExplicitByColumnAndRow(35, $row, ($record->getContributor()));
        $activeSheet->setCellValueExplicitByColumnAndRow(36, $row, $record->getGeneration());
        $activeSheet->setCellValueExplicitByColumnAndRow(37, $row, $record->getPart());
        $activeSheet->setCellValueExplicitByColumnAndRow(38, $row, $record->getCopyrightRestrictions());
        $activeSheet->setCellValueExplicitByColumnAndRow(39, $row, $record->getDuplicatesDerivatives());
        $activeSheet->setCellValueExplicitByColumnAndRow(40, $row, $record->getRelatedMaterial());
        $activeSheet->setCellValueExplicitByColumnAndRow(41, $row, $record->getConditionNote());
        $activeSheet->setCellValueExplicitByColumnAndRow(42, $row, $record->getCreatedOn()->format('Y-m-d H:i:s'));
        $activeSheet->setCellValueExplicitByColumnAndRow(43, $row, ($record->getUpdatedOn()) ? $record->getUpdatedOn()->format('Y-m-d H:i:s') : '');
        $activeSheet->setCellValueExplicitByColumnAndRow(44, $row, $record->getUser()->getName());

        if ($record->getAudioRecord()) {
            $activeSheet->setCellValueExplicitByColumnAndRow(10, $row, ($record->getAudioRecord()->getMediaDuration()) ? $record->getAudioRecord()->getMediaDuration() : "");
            $activeSheet->setCellValueExplicitByColumnAndRow(13, $row, ($record->getAudioRecord()->getBases()) ? $record->getAudioRecord()->getBases()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(15, $row, ($record->getAudioRecord()->getDiskDiameters()) ? $record->getAudioRecord()->getDiskDiameters()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(17, $row, ($record->getAudioRecord()->getMediaDiameters()) ? $record->getAudioRecord()->getMediaDiameters()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(21, $row, ($record->getAudioRecord()->getTapeThickness()) ? $record->getAudioRecord()->getTapeThickness()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(22, $row, ($record->getAudioRecord()->getSlides()) ? $record->getAudioRecord()->getSlides()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(23, $row, ($record->getAudioRecord()->getTrackTypes()) ? $record->getAudioRecord()->getTrackTypes()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(24, $row, ($record->getAudioRecord()->getMonoStereo()) ? $record->getAudioRecord()->getMonoStereo()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(25, $row, ($record->getAudioRecord()->getNoiceReduction()) ? $record->getAudioRecord()->getNoiceReduction()->getName() : '');
        }
        if ($record->getFilmRecord()) {
            $activeSheet->setCellValueExplicitByColumnAndRow(14, $row, ($record->getFilmRecord()->getPrintType()) ? $record->getFilmRecord()->getPrintType()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(18, $row, ($record->getFilmRecord()->getFootage()) ? $record->getFilmRecord()->getFootage() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(20, $row, ($record->getFilmRecord()->getColors()) ? $record->getFilmRecord()->getColors()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(29, $row, ($record->getFilmRecord()->getReelCore()) ? $record->getFilmRecord()->getReelCore()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(30, $row, ($record->getFilmRecord()->getSound()) ? $record->getFilmRecord()->getSound()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(31, $row, ($record->getFilmRecord()->getFrameRate()) ? $record->getFilmRecord()->getFrameRate()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(32, $row, ($record->getFilmRecord()->getAcidDetectionStrip()) ? $record->getFilmRecord()->getAcidDetectionStrip()->getName() : "");
            $activeSheet->setCellValueExplicitByColumnAndRow(33, $row, ($record->getFilmRecord()->getShrinkage()) ? $record->getFilmRecord()->getShrinkage() : '');
        }
        if ($record->getVideoRecord()) {
            $activeSheet->setCellValueExplicitByColumnAndRow(19, $row, ($record->getVideoRecord()->getRecordingSpeed()) ? $record->getVideoRecord()->getRecordingSpeed()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(26, $row, ($record->getVideoRecord()->getCassetteSize()) ? $record->getVideoRecord()->getCassetteSize()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(27, $row, ($record->getVideoRecord()->getFormatVersion()) ? $record->getVideoRecord()->getFormatVersion()->getName() : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(28, $row, ($record->getVideoRecord()->getRecordingStandard()) ? $record->getVideoRecord()->getRecordingStandard()->getName() : '');
        }
    }

    public function appendCellValuesByObject($record, $row)
    {
        $newRow = null;
        $newRow['project'] = $record->getProject() ? $record->getProject()->getName() : '';
        $newRow['collection_name'] = $record->getCollectionName();
        $newRow['media_type'] = $record->getMediaType() ? $record->getMediaType()->getName() : '';
        $newRow['unique_id'] = $record->getUniqueId();
        $newRow['location'] = $record->getLocation();
        $newRow['format'] = $record->getFormat()->getName() ? $record->getFormat()->getName() : '';
        $newRow['title'] = $record->getTitle();
        $newRow['description'] = $record->getDescription();
        $newRow['commercial'] = $record->getCommercial() ? $record->getCommercial()->getName() : '';
        $newRow['content_duration'] = $record->getContentDuration();
        $newRow['creation_date'] = $record->getCreationDate();
        $newRow['content_date'] = $record->getContentDate();
        $newRow['reel_diameter'] = $record->getReelDiameters() ? $record->getReelDiameters()->getName() : '';
        $newRow['genre_terms'] = $record->getGenreTerms();
        $newRow['contributor'] = $record->getContributor();
        $newRow['generation'] = $record->getGeneration();
        $newRow['part'] = $record->getPart();
        $newRow['copyright_restrictions'] = $record->getCopyrightRestrictions();
        $newRow['duplicates_derivatives'] = $record->getDuplicatesDerivatives();
        $newRow['related_material'] = $record->getRelatedMaterial();
        $newRow['condition_note'] = $record->getConditionNote();
        $newRow['created_on'] = $record->getCreatedOn()->format('Y-m-d H:i:s');
        $newRow['updated_on'] = $record->getUpdatedOn() ? $record->getUpdatedOn()->format('Y-m-d H:i:s') : '';
        $newRow['user_name'] = $record->getUser()->getName();

        if ($row['media_type'] == 'Audio') {
            $newRow['media_duration'] = $record->getAudioRecord()->getMediaDuration();
            $newRow['base'] = $record->getAudioRecord()->getBases() ? $record->getAudioRecord()->getBases()->getName() : "";
            $newRow['disk_diameter'] = $record->getAudioRecord()->getDiskDiameters() ? $record->getAudioRecord()->getDiskDiameters()->getName() : "";
            $newRow['media_diameter'] = $record->getAudioRecord()->getMediaDiameters() ? $record->getAudioRecord()->getMediaDiameters()->getName() : "";
            $newRow['tape_thickness'] = $record->getAudioRecord()->getTapeThickness() ? $record->getAudioRecord()->getTapeThickness()->getName() : "";
            $newRow['slides'] = $record->getAudioRecord()->getSlides() ? $record->getAudioRecord()->getSlides()->getName() : "";
            $newRow['track_type'] = $record->getAudioRecord()->getTrackTypes() ? $record->getAudioRecord()->getTrackTypes()->getName() : "";
            $newRow['mono_stereo'] = $record->getAudioRecord()->getMonoStereo() ? $record->getAudioRecord()->getMonoStereo()->getName() : "";
            $newRow['noice_reduction'] = $record->getAudioRecord()->getNoiceReduction() ? $record->getAudioRecord()->getNoiceReduction()->getName() : "";
        }
        if ($row['media_type'] == 'Film') {
            $newRow['print_type'] = $record->getFilmRecord()->getPrintType() ? $record->getFilmRecord()->getPrintType()->getName() : "";
            $newRow['footage'] = $record->getFilmRecord()->getFootage() ? $record->getFilmRecord()->getFootage()->getName() : "";
            $newRow['color'] = $record->getFilmRecord()->getColors() ? $record->getFilmRecord()->getColors()->getName() : "";
            $newRow['reel_core'] = $record->getFilmRecord()->getReelCore() ? $record->getFilmRecord()->getReelCore()->getName() : "";
            $newRow['sound'] = $record->getFilmRecord()->getSound() ? $record->getFilmRecord()->getSound()->getName() : "";
            $newRow['frame_rate'] = $record->getFilmRecord()->getFrameRate() ? $record->getFilmRecord()->getFrameRate()->getName() : "";
            $newRow['acid_detection'] = $record->getFilmRecord()->getAcidDetectionStrip() ? $record->getFilmRecord()->getAcidDetectionStrip()->getName() : "";
            $newRow['shrinkage'] = $record->getFilmRecord()->getShrinkage() ? $record->getFilmRecord()->getShrinkage()->getName() : "";
        }
        if ($row['media_type'] == 'Video') {
            $newRow['recording_speed'] = $record->getVideoRecord()->getRecordingSpeed() ? $record->getVideoRecord()->getRecordingSpeed()->getName() : "";
            $newRow['cassette_size'] = $record->getVideoRecord()->getCassetteSize() ? $record->getVideoRecord()->getCassetteSize()->getName() : "";
            $newRow['format_version'] = $record->getVideoRecord()->getFormatVersion() ? $record->getVideoRecord()->getFormatVersion()->getName() : "";
            $newRow['media_duration'] = $record->getVideoRecord()->getRecordingStandard() ? $record->getVideoRecord()->getRecordingStandard()->getName() : "";
        }
        $newRow['external_project'] = $row['project_name'];
        $newRow['external_collection_name'] = $row['collection_name'];
        $newRow['external_media_type'] = $row['media_type'];
        $newRow['external_unique_id'] = $row['unique_id'];
        $newRow['external_location'] = $row['location'];
        $newRow['external_format'] = $row['format'];
        $newRow['external_title'] = $row['title'];
        $newRow['external_description'] = $row['description'];
        $newRow['external_commercial'] = $row['commercial_or_unique'];
        $newRow['external_content_duration'] = $row['content_duration'];
        $newRow['external_creation_date'] = $row['creation_date'];
        $newRow['external_content_date'] = $row['content_date'];
        $newRow['external_reel_diameter'] = $row['reel_diameter'];
        $newRow['external_genre_terms'] = $row['genre_terms'];
        $newRow['external_contributor'] = $row['contributor'];
        $newRow['external_generation'] = $row['generation'];
        $newRow['external_part'] = $row['part'];
        $newRow['external_copyright_restrictions'] = $row['copyright_/_restrictions'];
        $newRow['external_duplicates_derivatives'] = $row['duplicates_/_derivatives'];
        $newRow['external_related_material'] = $row['related_material'];
        $newRow['external_condition_note'] = $row['condition_note'];
        $newRow['external_created_on'] = $row['time_stamp'];
        $newRow['external_updated_on'] = $row['timestamp_-_last_change'];
        $newRow['external_user_name'] = $row['cataloger'];
        if ($row['media_type'] == 'Audio') {
            $newRow['external_media_duration'] = $row['media_duration'];
            $newRow['external_base'] = $row['base'];
            $newRow['external_disk_diameter'] = $row['disk_diameter'];
            $newRow['external_media_diameter'] = $row['media_diameter'];
            $newRow['external_tape_thickness'] = $row['tape_thickness'];
            $newRow['external_slides'] = $row['sides'];
            $newRow['external_track_type'] = $row['track_type'];
            $newRow['external_mono_stereo'] = $row['mono_or_stereo'];
            $newRow['external_noice_reduction'] = $row['noise_reduction'];
        }
        if ($row['media_type'] == 'Film') {
            $newRow['external_print_type'] = $row['print_type'];
            $newRow['external_footage'] = $row['footage'];
            $newRow['external_color'] = $row['color'];
            $newRow['external_reel_core'] = $row['reel_core'];
            $newRow['external_sound'] = $row['sound'];
            $newRow['external_frame_rate'] = $row['frame_rate'];
            $newRow['external_acid_detection'] = $row['acid_detection'];
            $newRow['external_shrinkage'] = $row['shrinkage'];
        }
        if ($row['media_type'] == 'Video') {
            $newRow['external_recording_speed'] = $row['recording_speed'];
            $newRow['external_cassette_size'] = $row['cassette_size'];
            $newRow['external_format_version'] = $row['format_version'];
            $newRow['external_media_duration'] = $row['media_duration'];
        }
        return $newRow;
    }

    /**
     * Get records from sphinx for merge export file
     *
     * @param type $user
     * @param type $sphinxInfo
     * @param type $sphinxCriteria
     * @param type $em
     *
     * @return type
     */
    public function fetchFromSphinxToMerge($user, $sphinxInfo, $sphinxCriteria, $em, $mergeToFile)
    {
        $phpExcelObject = $this->initReport();
        $row = 2;
        $count = 0;
        $offset = 0;
        $sphinxObj = new SphinxSearch($em, $sphinxInfo);
        while ($count == 0) {
            $records = $sphinxObj->select($user, $offset, 1000, 'title', 'asc', $sphinxCriteria);
            $rec = $records[0];
            $totalFound = $records[1][1]['Value'];
            $phpExcelObject = $this->megerArrayRecords($rec, $mergeToFile, $phpExcelObject);
            $offset = $offset + 1000;
            $row++;
            if ($totalFound < 1000) {
                $count++;
            }
        }
        $phpExcelObject->setActiveSheetIndex(0);

        return $phpExcelObject;
    }

    public function makeExcelRowsByArray($activeSheet, $record, $mergRow, $row)
    {
        if ($record) {
            $activeSheet->setCellValueExplicitByColumnAndRow(0, $row, $record['project']);
            $activeSheet->setCellValueExplicitByColumnAndRow(1, $row, $record['collection_name']);
            $activeSheet->setCellValueExplicitByColumnAndRow(2, $row, $record['media_type']);
            $activeSheet->setCellValueExplicitByColumnAndRow(3, $row, $record['unique_id']);
            $activeSheet->setCellValueExplicitByColumnAndRow(4, $row, $record['location']);
            $activeSheet->setCellValueExplicitByColumnAndRow(5, $row, $record['format']);
            $activeSheet->setCellValueExplicitByColumnAndRow(6, $row, $record['title']);
            $activeSheet->setCellValueExplicitByColumnAndRow(7, $row, $record['description']);
            $activeSheet->setCellValueExplicitByColumnAndRow(8, $row, $record['commercial']);
            $activeSheet->setCellValueExplicitByColumnAndRow(9, $row, $record['content_duration']);
            $activeSheet->setCellValueExplicitByColumnAndRow(11, $row, $record['creation_date']);
            $activeSheet->setCellValueExplicitByColumnAndRow(12, $row, $record['content_date']);
            $activeSheet->setCellValueExplicitByColumnAndRow(16, $row, $record['reel_diameter']);
            $activeSheet->setCellValueExplicitByColumnAndRow(34, $row, $record['genre_terms']);
            $activeSheet->setCellValueExplicitByColumnAndRow(35, $row, $record['contributor']);
            $activeSheet->setCellValueExplicitByColumnAndRow(36, $row, $record['generation']);
            $activeSheet->setCellValueExplicitByColumnAndRow(37, $row, $record['part']);
            $activeSheet->setCellValueExplicitByColumnAndRow(38, $row, $record['copyright_restrictions']);
            $activeSheet->setCellValueExplicitByColumnAndRow(39, $row, $record['duplicates_derivatives']);
            $activeSheet->setCellValueExplicitByColumnAndRow(40, $row, $record['related_material']);
            $activeSheet->setCellValueExplicitByColumnAndRow(41, $row, $record['condition_note']);
            $activeSheet->setCellValueExplicitByColumnAndRow(42, $row, ($record['created_on']) ? $record['created_on'] : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(43, $row, ($record['updated_on']) ? $record['updated_on'] : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(44, $row, $record['user_name']);

            if ($record['media_type'] == 'Audio') {
                $activeSheet->setCellValueExplicitByColumnAndRow(10, $row, $record['media_duration']);
                $activeSheet->setCellValueExplicitByColumnAndRow(13, $row, $record['base']);
                $activeSheet->setCellValueExplicitByColumnAndRow(15, $row, $record['disk_diameter']);
                $activeSheet->setCellValueExplicitByColumnAndRow(17, $row, $record['media_diameter']);
                $activeSheet->setCellValueExplicitByColumnAndRow(21, $row, $record['tape_thickness']);
                $activeSheet->setCellValueExplicitByColumnAndRow(22, $row, $record['slides']);
                $activeSheet->setCellValueExplicitByColumnAndRow(23, $row, $record['track_type']);
                $activeSheet->setCellValueExplicitByColumnAndRow(24, $row, $record['mono_stereo']);
                $activeSheet->setCellValueExplicitByColumnAndRow(25, $row, $record['noice_reduction']);
            }
            if ($record['media_type'] == 'Film') {
                $activeSheet->setCellValueExplicitByColumnAndRow(14, $row, $record['print_type']);
                $activeSheet->setCellValueExplicitByColumnAndRow(18, $row, $record['footage']);
                $activeSheet->setCellValueExplicitByColumnAndRow(20, $row, $record['color']);
                $activeSheet->setCellValueExplicitByColumnAndRow(29, $row, $record['reel_core']);
                $activeSheet->setCellValueExplicitByColumnAndRow(30, $row, $record['sound']);
                $activeSheet->setCellValueExplicitByColumnAndRow(31, $row, $record['frame_rate']);
                $activeSheet->setCellValueExplicitByColumnAndRow(32, $row, $record['acid_detection']);
                $activeSheet->setCellValueExplicitByColumnAndRow(33, $row, $record['shrinkage']);
            }
            if ($record['media_type'] == 'Video') {
                $activeSheet->setCellValueExplicitByColumnAndRow(19, $row, $record['recording_speed']);
                $activeSheet->setCellValueExplicitByColumnAndRow(26, $row, $record['cassette_size']);
                $activeSheet->setCellValueExplicitByColumnAndRow(27, $row, $record['format_version']);
                $activeSheet->setCellValueExplicitByColumnAndRow(28, $row, $record['media_duration']);
            }
        }
        if ($mergRow) {
            $activeSheet->setCellValueExplicitByColumnAndRow(45, $row, $mergRow['external_project']);
            $activeSheet->setCellValueExplicitByColumnAndRow(46, $row, $mergRow['external_collection_name']);
            $activeSheet->setCellValueExplicitByColumnAndRow(47, $row, $mergRow['external_media_type']);
            $activeSheet->setCellValueExplicitByColumnAndRow(48, $row, $mergRow['external_unique_id']);
            $activeSheet->setCellValueExplicitByColumnAndRow(49, $row, $mergRow['external_location']);
            $activeSheet->setCellValueExplicitByColumnAndRow(50, $row, $mergRow['external_format']);
            $activeSheet->setCellValueExplicitByColumnAndRow(51, $row, $mergRow['external_title']);
            $activeSheet->setCellValueExplicitByColumnAndRow(52, $row, $mergRow['external_description']);
            $activeSheet->setCellValueExplicitByColumnAndRow(53, $row, $mergRow['external_commercial']);
            $activeSheet->setCellValueExplicitByColumnAndRow(54, $row, $mergRow['external_content_duration']);
            $activeSheet->setCellValueExplicitByColumnAndRow(56, $row, $mergRow['external_creation_date']);
            $activeSheet->setCellValueExplicitByColumnAndRow(57, $row, $mergRow['external_content_date']);
            $activeSheet->setCellValueExplicitByColumnAndRow(61, $row, $mergRow['external_reel_diameter']);
            $activeSheet->setCellValueExplicitByColumnAndRow(34, $row, $mergRow['external_genre_terms']);
            $activeSheet->setCellValueExplicitByColumnAndRow(35, $row, $mergRow['external_contributor']);
            $activeSheet->setCellValueExplicitByColumnAndRow(36, $row, $mergRow['external_generation']);
            $activeSheet->setCellValueExplicitByColumnAndRow(37, $row, $mergRow['external_part']);
            $activeSheet->setCellValueExplicitByColumnAndRow(38, $row, $mergRow['external_copyright_restrictions']);
            $activeSheet->setCellValueExplicitByColumnAndRow(39, $row, $mergRow['external_duplicates_derivatives']);
            $activeSheet->setCellValueExplicitByColumnAndRow(40, $row, $mergRow['external_related_material']);
            $activeSheet->setCellValueExplicitByColumnAndRow(41, $row, $mergRow['external_condition_note']);
            $activeSheet->setCellValueExplicitByColumnAndRow(42, $row, ($mergRow['external_created_on']) ? $mergRow['external_created_on'] : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(43, $row, ($mergRow['external_updated_on']) ? $mergRow['external_updated_on'] : '');
            $activeSheet->setCellValueExplicitByColumnAndRow(44, $row, $mergRow['external_user_name']);

            if ($mergRow['external_media_type'] == 'Audio') {
                $activeSheet->setCellValueExplicitByColumnAndRow(55, $row, $mergRow['external_media_duration']);
                $activeSheet->setCellValueExplicitByColumnAndRow(58, $row, $mergRow['external_base']);
                $activeSheet->setCellValueExplicitByColumnAndRow(60, $row, $mergRow['external_disk_diameter']);
                $activeSheet->setCellValueExplicitByColumnAndRow(62, $row, $mergRow['external_media_diameter']);
                $activeSheet->setCellValueExplicitByColumnAndRow(66, $row, $mergRow['external_tape_thickness']);
                $activeSheet->setCellValueExplicitByColumnAndRow(67, $row, $mergRow['external_slides']);
                $activeSheet->setCellValueExplicitByColumnAndRow(68, $row, $mergRow['external_track_type']);
                $activeSheet->setCellValueExplicitByColumnAndRow(69, $row, $mergRow['external_mono_stereo']);
                $activeSheet->setCellValueExplicitByColumnAndRow(70, $row, $mergRow['external_noice_reduction']);
            }
            if ($mergRow['external_media_type'] == 'Film') {
                $activeSheet->setCellValueExplicitByColumnAndRow(59, $row, $mergRow['external_print_type']);
                $activeSheet->setCellValueExplicitByColumnAndRow(63, $row, $mergRow['external_footage']);
                $activeSheet->setCellValueExplicitByColumnAndRow(65, $row, $mergRow['external_color']);
                $activeSheet->setCellValueExplicitByColumnAndRow(74, $row, $mergRow['external_reel_core']);
                $activeSheet->setCellValueExplicitByColumnAndRow(75, $row, $mergRow['external_sound']);
                $activeSheet->setCellValueExplicitByColumnAndRow(76, $row, $mergRow['external_frame_rate']);
                $activeSheet->setCellValueExplicitByColumnAndRow(77, $row, $mergRow['external_acid_detection']);
                $activeSheet->setCellValueExplicitByColumnAndRow(78, $row, $mergRow['external_shrinkage']);
            }
            if ($mergRow['external_media_type'] == 'Video') {
                $activeSheet->setCellValueExplicitByColumnAndRow(64, $row, $mergRow['external_recording_speed']);
                $activeSheet->setCellValueExplicitByColumnAndRow(71, $row, $mergRow['external_cassette_size']);
                $activeSheet->setCellValueExplicitByColumnAndRow(72, $row, $mergRow['external_format_version']);
                $activeSheet->setCellValueExplicitByColumnAndRow(73, $row, $mergRow['external_media_duration']);
            }
        }
    }

    public function appendCellValuesByArray($record, $row)
    {
        $newRow = null;
        $newRow['project'] = $row['project_name'] ? $record['project'] . ' ' . $row['project_name'] : $record['project'];
        $newRow['collection_name'] = $row['collection_name'] ? $record['collection_name'] . ' ' . $row['collection_name'] : $record['collection_name'];
        $newRow['media_type'] = $row['media_type'] ? $record['media_type'] . ' ' . $row['media_type'] : $record['media_type'];
        $newRow['unique_id'] = $record['unique_id'];
        $newRow['location'] = $row['location'] ? $record['location'] . ' ' . $row['location'] : $record['location'];
        $newRow['format'] = $row['format'] ? $record['format'] . ' ' . $row['format'] : $record['format'];
        $newRow['title'] = $row['title'] ? $record['title'] . '' . $row['title'] : $record['title'];
        $newRow['description'] = $row['description'] ? $record['description'] . '' . $row['description'] : $record['description'];
        $newRow['commercial'] = $row['commercial_or_unique'] ? $record['commercial'] . ' ' . $row['commercial_or_unique'] : $record['commercial'];
        $newRow['content_duration'] = $row['content_duration'] ? $record['content_duration'] . ' ' . $row['content_duration'] : $record['content_duration'];
        $newRow['creation_date'] = $row['creation_date'] ? $record['creation_date'] . ' ' . $row['creation_date'] : $record['creation_date'];
        $newRow['content_date'] = $row['content_date'] ? $record['content_date'] . ' ' . $row['content_date'] : $record['content_date'];
        $newRow['reel_diameter'] = $row['reel_diameter'] ? $record['reel_diameter'] . ' ' . $row['reel_diameter'] : $record['reel_diameter'];
        $newRow['genre_terms'] = $row['genre_terms'] ? $record['genre_terms'] . ' ' . $row['genre_terms'] : $record['genre_terms'];
        $newRow['contributor'] = $row['contributor'] ? $record['contributor'] . ' ' . $row['contributor'] : $record['contributor'];
        $newRow['generation'] = $row['generation'] ? $record['generation'] . ' ' . $row['generation'] : $record['generation'];
        $newRow['part'] = $row['part'] ? $record['part'] . ' ' . $row['part'] : $record['part'];
        $newRow['copyright_restrictions'] = $row['copyright_/_restrictions'] ? $record['copyright_restrictions'] . ' ' . $row['copyright_/_restrictions'] : $record['copyright_restrictions'];
        $newRow['duplicates_derivatives'] = $row['duplicates_/_derivatives'] ? $record['duplicates_derivatives'] . ' ' . $row['genre_terms'] : $record['duplicates_derivatives'];
        $newRow['related_material'] = $row['related_material'] ? $record['related_material'] . ' ' . $row['duplicates_/_derivatives'] : $record['related_material'];
        $newRow['condition_note'] = $row['condition_note'] ? $record['condition_note'] . ' ' . $row['condition_note'] : $record['condition_note'];
        $newRow['created_on'] = ($row['time_stamp']) ? $record['created_on'] . ' ' . $row['time_stamp'] : $record['created_on'];
        $newRow['updated_on'] = $row['timestamp_-_last_change'] ? $record['updated_on'] . ' ' . $row['timestamp_-_last_change'] : $record['updated_on'];
        $newRow['user_name'] = $row['cataloger'] ? $record['user_name'] . ' ' . $row['cataloger'] : $record['user_name'];

        if ($row['media_type'] == 'Audio') {
            $newRow['media_duration'] = $row['media_duration'] ? $record['media_duration'] . ' ' . $row['media_duration'] : $record['media_duration'];
            $newRow['base'] = $row['base'] ? $record['base'] . ' ' . $row['base'] : $record['base'];
            $newRow['disk_diameter'] = $row['disk_diameter'] ? $record['disk_diameter'] . ' ' . $row['disk_diameter'] : $record['disk_diameter'];
            $newRow['media_diameter'] = $row['media_diameter'] ? $record['media_diameter'] . ' ' . $row['media_diameter'] : $record['media_diameter'];
            $newRow['tape_thickness'] = $row['tape_thickness'] ? $record['tape_thickness'] . ' ' . $row['tape_thickness'] : $record['tape_thickness'];
            $newRow['slides'] = $row['sides'] ? $record['slides'] . ' ' . $row['sides'] : $record['slides'];
            $newRow['track_type'] = $row['track_type'] ? $record['track_type'] . ' ' . $row['track_type'] : $record['track_type'];
            $newRow['mono_stereo'] = $row['mono_or_stereo'] ? $record['mono_stereo'] . ' ' . $row['mono_or_stereo'] : $record['mono_stereo'];
            $newRow['noice_reduction'] = $row['noise_reduction'] ? $record['noice_reduction'] . ' ' . $row['noise_reduction'] : $record['noice_reduction'];
        }
        if ($row['media_type'] == 'Film') {
            $newRow['print_type'] = $row['print_type'] ? $record['print_type'] . ' ' . $row['print_type'] : $record['print_type'];
            $newRow['footage'] = $row['footage'] ? $record['footage'] . ' ' . $row['footage'] : $record['footage'];
            $newRow['color'] = $row['color'] ? $record['color'] . ' ' . $row['color'] : $record['color'];
            $newRow['reel_core'] = $row['reel_core'] ? $record['reel_core'] . ' ' . $row['reel_core'] : $record['reel_core'];
            $newRow['sound'] = $row['sound'] ? $record['sound'] . ' ' . $row['sound'] : $record['sound'];
            $newRow['frame_rate'] = $row['frame_rate'] ? $record['frame_rate'] . ' ' . $row['frame_rate'] : $record['frame_rate'];
            $newRow['acid_detection'] = $row['acid_detection'] ? $record['acid_detection'] . ' ' . $row['acid_detection'] : $record['acid_detection'];
            $newRow['shrinkage'] = $row['shrinkage'] ? $record['shrinkage'] . ' ' . $row['shrinkage'] : $record['shrinkage'];
        }
        if ($row['media_type'] == 'Video') {
            $newRow['recording_speed'] = $row['recording_speed'] ? $record['recording_speed'] . ' ' . $row['recording_speed'] : $record['recording_speed'];
            $newRow['cassette_size'] = $row['cassette_size'] ? $record['cassette_size'] . ' ' . $row['cassette_size'] : $record['cassette_size'];
            $newRow['format_version'] = $row['format_version'] ? $record['format_version'] . ' ' . $row['format_version'] : $record['format_version'];
            $newRow['media_duration'] = $row['media_duration'] ? $record['media_duration'] . ' ' . $row['print_type'] : $record['media_duration'];
        }

        return $newRow;
    }

    public function generatePrioritizationReport($records)
    {
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("AVCC - AVPreserve")
                ->setTitle("AVCC - Report")
                ->setSubject("Prioritization Report")
                ->setDescription("Prioritization Report");
        $activeSheet = $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle('Prioritization Report');
        $row = 1;
// Prepare header row for report
        $this->preparePrioritizationHeader($activeSheet, $row);
        $row ++;
        $this->preparePrioritizationRecords($activeSheet, $row, $records);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        return $phpExcelObject;
    }

    /**
     * Create the Header for report.
     *
     * @param  PHPExcel_Worksheet $activeSheet
     * @param  Integer            $row
     * @return boolean
     */
    private function preparePrioritizationHeader($activeSheet, $row)
    {
        $columns = new ExportFields();
        $this->columns = $columns->getPrioritizationColumns();
        foreach ($this->columns as $column => $columnName) {
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $row, str_replace('_', ' ', $columnName));
            $activeSheet->getColumnDimensionByColumn($column)->setWidth(20);
            $activeSheet->getStyleByColumnAndRow($column)->getFont()->setBold(true);
        }

        return TRUE;
    }

    /**
     * Prepare rows for records.
     *
     * @param  PHPExcel_Worksheet $activeSheet
     * @param  Integer            $row
     * @return boolean
     */
    private function preparePrioritizationRecords($activeSheet, $row, $records)
    {
        foreach ($records as $record) {
            $score = 0;
            $activeSheet->setCellValueExplicitByColumnAndRow(0, $row, $record->getProject());
            $activeSheet->setCellValueExplicitByColumnAndRow(1, $row, $record->getCollectionName());
            $activeSheet->setCellValueExplicitByColumnAndRow(2, $row, $record->getTitle());
            $activeSheet->setCellValueExplicitByColumnAndRow(3, $row, $record->getUniqueId());
            $score = $score + (float) (($record->getMediaType()) ? $record->getMediaType()->getScore() : 0);
            $score = $score + (float) (($record->getFormat()) ? $record->getFormat()->getScore() : 0);
            $score = $score + (float) (($record->getCommercial()) ? $record->getCommercial()->getScore() : 0);
            $score = $score + (float) (($record->getReelDiameters()) ? $record->getReelDiameters()->getScore() : 0);

            if ($record->getAudioRecord()) {
                //    $score = $score + ($record->getAudioRecord()->getMediaDuration()) ? $record->getAudioRecord()->getMediaDuration()->getscore() : 0;
                $score = $score + (float) (($record->getAudioRecord()->getBases()) ? $record->getAudioRecord()->getBases()->getScore() : 0);
                $score = $score + (float) (($record->getAudioRecord()->getDiskDiameters()) ? $record->getAudioRecord()->getDiskDiameters()->getScore() : 0);
                $score = $score + (float) (($record->getAudioRecord()->getMediaDiameters()) ? $record->getAudioRecord()->getMediaDiameters()->getScore() : 0);
                $score = $score + (float) (($record->getAudioRecord()->getTapeThickness()) ? $record->getAudioRecord()->getTapeThickness()->getScore() : 0);
                $score = $score + (float) (($record->getAudioRecord()->getSlides()) ? $record->getAudioRecord()->getSlides()->getScore() : 0);
                $score = $score + (float) (($record->getAudioRecord()->getTrackTypes()) ? $record->getAudioRecord()->getTrackTypes()->getScore() : 0);
                $score = $score + (float) (($record->getAudioRecord()->getMonoStereo()) ? $record->getAudioRecord()->getMonoStereo()->getScore() : 0);
                $score = $score + (float) (($record->getAudioRecord()->getNoiceReduction()) ? $record->getAudioRecord()->getNoiceReduction()->getScore() : 0);
            }
            if ($record->getFilmRecord()) {
                $score = $score + (float) (($record->getFilmRecord()->getPrintType()) ? $record->getFilmRecord()->getPrintType()->getScore() : 0);
                //  $score = $score + ($record->getFilmRecord()->getFootage()) ? $record->getFilmRecord()->getscore() : 0;
                $score = $score + (float) (($record->getFilmRecord()->getColors()) ? $record->getFilmRecord()->getColors()->getScore() : 0);
                $score = $score + (float) (($record->getFilmRecord()->getReelCore()) ? $record->getFilmRecord()->getReelCore()->getScore() : 0);
                $score = $score + (float) (($record->getFilmRecord()->getSound()) ? $record->getFilmRecord()->getSound()->getScore() : 0);
                $score = $score + (float) (($record->getFilmRecord()->getFrameRate()) ? $record->getFilmRecord()->getFrameRate()->getScore() : 0);
                $score = $score + (float) (($record->getFilmRecord()->getAcidDetectionStrip()) ? $record->getFilmRecord()->getAcidDetectionStrip()->getScore() : 0);
                //  $score = $score + ($record->getFilmRecord()->getShrinkage()) ? $record->getFilmRecord()->getscore() : 0;
            }
            if ($record->getVideoRecord()) {
                $score = $score + (float) (($record->getVideoRecord()->getRecordingSpeed()) ? $record->getVideoRecord()->getRecordingSpeed()->getScore() : 0);
                $score = $score + (float) (($record->getVideoRecord()->getCassetteSize()) ? $record->getVideoRecord()->getCassetteSize()->getScore() : 0);
                $score = $score + (float) (($record->getVideoRecord()->getFormatVersion()) ? $record->getVideoRecord()->getFormatVersion()->getScore() : 0);
                $score = $score + (float) (($record->getVideoRecord()->getRecordingStandard()) ? $record->getVideoRecord()->getRecordingStandard()->getScore() : 0);
            }
            $scale_score = ($score / 100) * 5;
            $activeSheet->setCellValueExplicitByColumnAndRow(4, $row, $scale_score);
            $row ++;
        }

        return true;
    }

    public function megerArrayRecords($records, $mergeToFile, $newphpExcelObject)
    {
        $mergeFileCompletePath = $this->container->getParameter('webUrl') . 'merge/' . date('Y') . '/' . date('m') . '/' . $mergeToFile;
        if (file_exists($mergeFileCompletePath)) {
            $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject($mergeFileCompletePath);
            $activeSheet = $newphpExcelObject->setActiveSheetIndex(0);
            foreach ($phpExcelObject->getWorksheetIterator() as $worksheet) {
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $excelCell = new PHPExcel_Cell(null, null, $worksheet);
                $highestColumnIndex = $excelCell->columnIndexFromString($highestColumn);
                if ($highestRow > 0) {
                    $rows = array();
                    $newRows = array();
                    $newrow = 2;
                    foreach ($records as $record) {
                        for ($row = 2; $row <= $highestRow; ++$row) {
                            for ($col = 0; $col < $highestColumnIndex; ++$col) {
                                $matched = false;
                                if ($record['unique_id'] == $worksheet->getCellByColumnAndRow(3, $row)) {
                                    $matched = true;
                                    $uniq = strtolower(str_replace(' ', '_', $record['unique_id']));
                                }
                                if ($matched) {
                                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                                    $columnName = strtolower(str_replace(' ', '_', $worksheet->getCellByColumnAndRow($col, 1)));
                                    $rows[$uniq][$columnName] = $cell->getValue();
                                }
                            }
                        }
                    }
                    foreach ($records as $record) {
                        $recUniq = strtolower(str_replace(' ', '_', $record['unique_id']));
                        if (array_key_exists($recUniq, $rows)) {
                            $newRows = $this->appendCellValuesByArray($record, $rows[$recUniq]);
                            if ($newRows)
                                $this->makeExcelRowsByArray($activeSheet, $newRows, $newrow);
                        } else {
                            $this->makeExcelRowsByArray($activeSheet, $record, $newrow);
                        }
                        $newrow ++;
                    }
                    if ($records) {
                        return $newphpExcelObject;
                    }
                } else {
                    return "The file $mergeToFile is empty";
                }
            }
        } else {
            return "The file $mergeToFile does not exist";
        }
    }

}
