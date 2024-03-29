<?php

namespace MarceloNees\CkEditor\TCkEditor;

use Adianti\Core\AdiantiApplicationConfig;
use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Base\TStyle;
use Adianti\Widget\Form\TField;
use Adianti\Widget\Util\TImage;

/**
 * TinyMceEditor
 *
 * @package    widget
 * @subpackage form
 * @author     Marcelo Barreto Nees <marcelo.linux@gmail.com>
 */
class TCkEditor extends TField implements AdiantiWidgetInterface
{
    protected $id;
    protected $size;
    protected $formName;
    protected $toolbar;
    protected $customButtons;
    protected $completion;
    protected $options;
    private   $height;

    /**
     * Class Constructor
     * @param $name Widget's name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->id               = 'TCkEditor_' . mt_rand(1000000000, 1999999999);
        $this->toolbar          = true;
        $this->options          = [];
        $this->customButtons    = [];
        // creates a tag
        $this->tag = new TElement('textarea');
        $this->tag->{'widget'} = 'TCkEditor';

        // TStyle::importFromFile('vendor/marcelonees/tckeditoradianti/src/TCkEditor/content.min.css');
        TScript::importFromFile('vendor/marcelonees/tckeditor/src/TCkEditor/ckeditor.js');

        // TScript::create("
        //     tinymce.init({
        //         selector: '#{$this->id}'
        //     });
        // ");
    }

    /**
     * Define max length
     * @param  $length Max length
     */
    public function setMaxLength($length)
    {
        if ($length > 0) {
            $this->options['maxlength'] = $length;
        }
    }

    /**
     * Set extra calendar options
     * @link https://summernote.org/deep-dive/
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Add custom button
     *
     * @link https://summernote.org/deep-dive/#custom-button
     * @param $name     String  name action
     * @param $function String  function(context){  }
     * @param $title    String  title icon
     * @param $icon     TImage  toolbar icon
     */
    public function addCustomButton($name, $function, $title, TImage $icon, $showLabel = false)
    {
        $this->customButtons[] = [
            'name' => $name,
            'function' => base64_encode($function),
            'title' => base64_encode($title),
            'showLabel' => $showLabel,
            'icon' => base64_encode($icon->getContents()),
        ];
    }

    /**
     * Define the widget's size
     * @param  $width   Widget's width
     * @param  $height  Widget's height
     */
    public function setSize($width, $height = NULL)
    {
        $this->size   = $width;
        if ($height) {
            $this->height = $height;
        }
    }

    /**
     * Returns the size
     * @return array(width, height)
     */
    public function getSize()
    {
        return array($this->size, $this->height);
    }

    /**
     * Disable toolbar
     */
    public function disableToolbar()
    {
        $this->toolbar = false;
    }

    /**
     * Define options for completion
     * @param $options array of options for completion
     */
    function setCompletion($options)
    {
        $this->completion = $options;
    }

    /**
     * Enable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function enableField($form_name, $field)
    {
        TScript::create(" TCkEditor_enable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Disable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function disableField($form_name, $field)
    {
        TScript::create(" TCkEditor_disable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Clear the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function clearField($form_name, $field)
    {
        TScript::create(" TCkEditor_clear_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Reload completion
     * 
     * @param $field Field name or id
     * @param $options array of options for autocomplete
     */
    public static function reloadCompletion($field, $options)
    {
        $options = json_encode($options);
        TScript::create(" thtml_editor_reload_completion( '{$field}', $options); ");
    }

    /**
     * Insert text
     * @param $form_name Form name
     * @param $field Field name
     * @param $content Text content
     */
    public static function insertText($form_name, $field, $content)
    {
        TScript::create(" TCkEditor_insert_text('{$form_name}', '{$field}', '{$content}'); ");
    }

    /**
     * Show the widget
     */
    public function show()
    {
        $this->tag->{'id'}      = $this->id;
        $this->tag->{'class'}   = 'tckeditor';   // CSS
        $this->tag->{'name'}    = $this->name;   // tag name

        $ini = AdiantiApplicationConfig::get();
        $locale = !empty($ini['general']['locale']) ? $ini['general']['locale'] : 'pt-BR';

        // add the content to the textarea
        $this->tag->add(htmlspecialchars((string) $this->value));

        // show the tag
        $div = new TElement('div');
        $div->style = 'display: none';
        $div->add($this->tag);
        $div->show();

        $options = $this->options;
        if (!$this->toolbar) {
            $options['airMode'] = true;
        }
        if (!empty($this->completion)) {
            $options['completion'] = $this->completion;
        }

        $options_json = json_encode($options);
        $buttons_json = json_encode($this->customButtons);
        // TScript::create(" TCkEditor_start( '{$this->tag->{'id'}}', '{$this->size}', '{$this->height}', '{$locale}', '{$options_json}', '{$buttons_json}' ); ");
        // TScript::create(" $('#{$this->tag->id}').parent().show();");


        TScript::create("
            console.log('Loading ckeditor...');
            $.getScript('vendor/marcelonees/tckeditor/src/TCkEditor/ckeditor.js', {'crossOrigin': 'anonymous', 'crossDomain': 'true',}).done(function(s, Status) {
                console.log('ckeditor script loaded');
                ClassicEditor
                    .create( document.querySelector( '#{$this->id}' ) )
                    .catch( error => {
                        console.error(error);
                    } 
                );
            }
        ");

        // check if the field is not editable
        if (!parent::getEditable()) {
            TScript::create(" TCkEditor_disable_field('{$this->formName}', '{$this->name}'); ");
        }
    }
}
