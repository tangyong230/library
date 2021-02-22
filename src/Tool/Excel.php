<?php
/**
 * filename:Excel.php
 * auhor:china.php@qq.com
 * datetime:2021/2/19
 */


namespace tangyong\library\Tool;


class Excel
{

    /** 设置表数据
     * @param array $rangeData
     * @param array $data
     */
    private static function setTableData($PHPExcel, array $rangeData, array $data, $index = 0)
    {
        foreach ($data as $key => $value) {
            $hao2 = $key + 2;
            $kv = array_keys($value);
            for ($i = 0; $i < count($rangeData); $i++) {
                //设置对齐方式
                $PHPExcel->setActiveSheetIndex($index)->getStyle($rangeData[$i] . $hao2)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $PHPExcel->setActiveSheetIndex($index)->getStyle($rangeData[$i] . $hao2)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                //设置行高
                $PHPExcel->setActiveSheetIndex($index)->getRowDimension($key + 2)->setRowHeight(80);
                //设置表数据
                $PHPExcel->setActiveSheetIndex($index)->setCellValue($rangeData[$i] . $hao2, $value[$kv[$i]]);
            }
        }
    }

    /** 设置表头
     * @param array $rangeData
     * @param array $header
     */
    private static function setTableHeader($PHPExcel, array $rangeData, array $header, $index = 0)
    {
        for ($i = 0; $i < count($rangeData); $i++) {
            $PHPExcel->setActiveSheetIndex($index)->setCellValue($rangeData[$i] . "1", $header[$i]);
            //设置对齐方式
            $PHPExcel->setActiveSheetIndex($index)->getStyle($rangeData[$i] . "1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $PHPExcel->setActiveSheetIndex($index)->getStyle($rangeData[$i] . "1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $PHPExcel->setActiveSheetIndex($index)->getStyle($rangeData[$i] . "1")->getFont()->setBold(true);
        }
    }

    /**　设置列宽
     * @param array $rangeData
     * @param $width
     * @param array $special
     */
    private static function setColumnWidth($PHPExcel, array $rangeData, $width = 25, $special = [], $index = 0)
    {
        for ($i = 0; $i < count($rangeData); $i++) {
            $PHPExcel->setActiveSheetIndex($index)->getColumnDimension($rangeData[$i])->setWidth($width);
            if (!empty($special)) {
                foreach ($special as $k => $v) {
                    if ($rangeData[$i] === $k) {
                        $PHPExcel->setActiveSheetIndex($index)->getColumnDimension($k)->setWidth($v);
                    }
                }
            }
        }
    }

    /** 根据数组长度生成excel a-z范围数组
     * @param $number 数组元素个数
     * @return array
     */
    private static function createRangeByData($number)
    {
        $arrRangeData = range("A", "Z");
        $newArrRageData = [];
        for ($i = 0; $i < $number; $i++) {
            $newArrRageData[] = $arrRangeData[$i];
        }
        return $newArrRageData;
    }

    /** 设置标题
     * @param $PHPExcel
     * @param $title
     * @param int $index
     */
    private static function setTitle($PHPExcel, $title, $index = 0)
    {
        $PHPExcel->setActiveSheetIndex($index)->setTitle($title);
    }

    /** 保存excel文件
     * @param $PHPExcel
     * @param $dirNamePath
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private static function saveExcel($PHPExcel, $dirNamePath)
    {
        $writer = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $fileName = uniqid() . ".xlsx";
        $writer->save($dirNamePath . $fileName);
    }

    /** 生成excel
     * @param $header
     * @param $result
     * @param $title
     * @param $dirNamePath
     * @param int $width
     * @param array $special
     * @param int $index
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public static function generateExcel($header, $result, $title, $dirNamePath,$width=25,$special=[],$index = 0)
    {
        $PHPExcel = new \PHPExcel();
        //生成range
        $rangeData = self::createRangeByData(count($result[0]));
        self::setTableHeader($PHPExcel, $rangeData, $header, $index);
        self::setTableData($PHPExcel, $rangeData, $result, $index);
        self::setColumnWidth($PHPExcel,$rangeData,$width,$special,$index);
        self::setTitle($PHPExcel, $title, $index);
        self::saveExcel($PHPExcel, $dirNamePath);
    }
}