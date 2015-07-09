<?php

class ResponsiveImageHtmlEditorField_Toolbar extends Extension{

	private static $allowed_actions = array(
		'getresampledimage'
	);

	public function updateFieldsForImage($fields, $url, $file){
		$sets = Config::inst()->get('ResponsiveImageExtension', 'sets');
		if(empty($sets)){
			return;
		}
		
		$options = array();
		foreach ($sets as $k => $v) {
			if(isset($v['wysiwyg']) && $v['wysiwyg']){
				$options[$k] = isset($v['description']) ? $v['description'] : $k;	
			}
		}
		if(empty($options)){
			return;
		}

		$width = $fields->dataFieldByName('Width');
	
		if($width){
			$resize_method_options = array(
				'Standard' => _t('ResponsiveWYSIWYGImages.RESIZEMETHOD_STANDARD', 'Standard'),
				'Responsive' => _t('ResponsiveWYSIWYGImages.RESIZEMETHOD_STANDARD', 'Responsive')
			);

			$fields->insertAfter(DropdownField::create(
				'ResizeMethod', 
				_t('ResponsiveWYSIWYGImages.RESIZEMETHOD', 'Resize Method'), 
				$resize_method_options), 
				'CSSClass'
			);	
		}

		$fields->insertAfter($responsiveSetField = DropdownField::create(
			'ResponsiveSet', 
			_t('ResponsiveWYSIWYGImages.IMAGEDIMENSIONS', 'Responsive Dimensions'), 
			$options),
			'ResizeMethod'
		);

		$fields->push(HiddenField::create('ID', null, $file->ID ));
	}

	public function updatemediaform($form){
		Requirements::javascript(RESPONSIVE_WYSIWYG_IMAGES_DIR . '/javascript/HTMLEditorField.js');
	}

	public function getresampledimage($request){
		$imageID = $request->getVar('id');
		$setName = $request->getVar('responsiveset');
		
		$sets = Config::inst()->get('ResponsiveImageExtension', 'sets');
		if(isset($sets[$setName]) && $image = Image::get()->byID($imageID)){
			$set = $sets[$setName];
			$size = $set['default_size'];
			$width = $size;
			$height = null;
			if(strpos($size, 'x') !== false) {
				$dimensions = explode("x", $size);
			}else{
				$dimensions = array($width, $height);
			}
	
			return $image->getFormattedImage($set['method'], $dimensions[0], $dimensions[1])->owner->Link();	
		}

		
	}
}