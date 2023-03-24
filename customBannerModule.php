<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class customBannerModule extends Module
{
    public function __construct()
    {
        $this->name = 'customBannerModule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Chitiga Alexandru Gabriel';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '8.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('customBannerModule');
        $this->description = $this->l('Description of my module.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');


    }
    public function install()
    {

        return (
            parent::install()
            && $this->registerHook('displayBannner')
            && $this->registerHook('displayFullWidthTop')
        );
    }
    public function uninstall()
    {
        return (
            parent::uninstall()
            && Configuration::deleteByName('customBannerModule')
        );
    }
    function deleteFiles($dir)
    {
        foreach(glob($dir . '/*') as $file){
            if(is_file($file)){
                unlink($file);
            }
        }
    }
    public function getContent()
    {
        $output = '';
        $message = '';

        if (Tools::isSubmit('submit' . $this->name)) {

            $target_dir = _PS_MODULE_DIR_ . "customBannerModule/views/img/";
            $target_file = $target_dir . basename($_FILES["MYMODULE_CONFIG"]["name"]);
            $this->deleteFiles($target_dir);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["MYMODULE_CONFIG"]["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                $this->_errors[]=$this->l('File is not an image.');
                $uploadOk = 0;
            }

            if ($_FILES["MYMODULE_CONFIG"]["size"] > 500000) {
                $this->_errors[]=$this->l('Sorry, your file is too large.');
                $uploadOk = 0;
            }
            if (
                $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif"
            ) {
                $this->_errors[]=$this->l('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
                $uploadOk = 0;
            }
            if ($uploadOk == 0) {
                $this->_errors[]=$this->l('Sorry, your file was not uploaded.');
            } else {
                if (move_uploaded_file($_FILES["MYMODULE_CONFIG"]["tmp_name"], $target_file)) {
                } else {
                    $this->_errors[]=$this->l('Sorry, there was an error uploading your file.');

                }

                $configValue = (string) Tools::getValue('MYMODULE_CONFIG');

                if (empty($configValue) || !Validate::isGenericName($configValue)) {
                    $output = $this->displayError($this->l('Invalid Configuration value'));
                } else {
                    Configuration::updateValue('MYMODULE_CONFIG', $configValue);
                    $output = $this->displayConfirmation($this->l('Settings updated'));
                }
            }

            if (count($this->_errors)) {
                $message = $this->displayError($this->_errors);
            } else {
                
            }

           
        }

         return $output . $message .$this->displayForm();
    }
    public function displayForm(){
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'file',
                        'label' => $this->l('Select image:'),
                        'name' => 'MYMODULE_CONFIG',
                        'desc' => $this->l('Upload a banner image to display on your website.'),
                        'size' => 12

                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'desc' => $this->l('Save'),
                    'class' => 'btn btn-default pull-left',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submit' . $this->name;



        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        $helper->fields_value['MYMODULE_CONFIG'] = Tools::getValue('MYMODULE_CONFIG', Configuration::get('MYMODULE_CONFIG'));

        return $helper->generateForm([$form]);
    }

    public function postProcess()
    {
        return parent::postProcess();
    }
    
    
    public function hookDisplayFullWidthTop(){
        $target_dir = Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/customBannerModule/views/img/';
        $image = null;
        if (Configuration::get('MYMODULE_CONFIG')) {
            $image = $target_dir.Configuration::get('MYMODULE_CONFIG');
        }

        $this->context->smarty->assign(
            array(
                'banner_image' => $image
            )
        );

        return $this->context->smarty->fetch($this->local_path . 'views/templates/front/custombannermodule.tpl');
    }

}