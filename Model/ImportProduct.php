<?php
namespace Lof\UrlKeyMask\Model;

class ImportProduct extends \Magento\CatalogImportExport\Model\Import\Product
{
    const XML_PATH_URL_KEY_MASK = 'catalog/fields_masks/url_key_mask';
    const XML_PATH_FORCE_IMPORT_IMAGE = 'catalog/fields_masks/force_import_image';

    /**
     * Uploading files into the "catalog/product" media folder.
     *
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @param bool $renameFileOff [optional] boolean to pass.
     * Default is false which will set not to rename the file after import.
     * @return string
     */
    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        $forceImportImage = $this->scopeConfig->getValue(
            self::XML_PATH_FORCE_IMPORT_IMAGE
        );
        if ($forceImportImage) {
            $firstChar = $fileName ? substr($fileName, 0, 1) : "";
            $secondChar = $fileName ? substr($fileName, 1, 1) : "";
            if ($firstChar != "" || $secondChar != "") {
                $fileName = "/".($firstChar ? $firstChar : "0")."/".($secondChar ? $secondChar : "0")."/".$fileName;
                return $fileName;
            } else {
                return "/placeholder/default/image-default.jpg";
            }
        }
        return parent::uploadMediaFiles($fileName, $renameFileOff);
    }

    /**
     * Retrieve url key from provided row data.
     *
     * @param array $rowData
     * @return string
     *
     * @since 100.0.3
     */
    protected function getUrlKey($rowData)
    {
        $key = $this->GetKeyDefault();
        $url_key = "";
        if($key && !empty($key))
        {
            $url_key = $this->ProcessTokenizedKey($key, $rowData);
            if (!empty($url_key)) {
                $rowData[self::URL_KEY] = $url_key;
            }
        }
        return parent::getUrlKey($rowData);
    }

    /**
     * Parse a default tokenized string to generate the final key
     *
     * @param string $token
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function ProcessTokenizedKey($token, $product) {

        $result = $token;

        preg_match_all('/\{\{([a-zA-Z1-9]*)\}\}/', $token, $preg_output);

        for($i = 0; $i < count($preg_output[1]); $i++)
        {
            $value = isset($product[$preg_output[1][$i]]) ? $product[$preg_output[1][$i]] : null;
            if(is_null($value))
            {
                $value = '';
            }

            $result = str_replace($preg_output[0][$i], $value, $result);
        }

        return $this->productUrl->formatUrlKey($result);
    }

    /**
     * Return stored default key
     *
     * @return string
     */
    protected function GetKeyDefault()
    {
        if(!isset($this->keyDefault))
        {
            $this->keyDefault = $this->scopeConfig->getValue(
                self::XML_PATH_URL_KEY_MASK
            );
        }
        return $this->keyDefault;
    }
}
