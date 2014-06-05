<?php
/*****************************************************************************/
/** @class HtmlElement
 *  @brief  This @b abstract @b class parses html elements
 *
 *  This abstract class is designed to parse html elements.
 *  It is only allowed to use extensions of this class.
 *  Create a html object and add your elements programmatically  .
 *  Calling as parent instance just define the element you need and add all inline elements
 *  or child elements. Also it is possible to define attributes and value for each added
 *  element. Content data can be passed as string or as array.
 *  The class supports also reading the data from assoc arrays and bi dimensional arrays.
 *  @par Testarray with data
 *  @code
 *  // Example content arrays
 *  $dataArray = array('Data 1', 'Data 2', 'Data 3');
 *  @endcode 
 *  @par Example_1: @b unorderedlist
 *  @code 
 *  // create as parent instance
 *  parent::HtmlElement('ul','class', 'unordered');  // Parameters( element, attribute, value, nesting (true/false ))
 *  // we want to have further attributes for the element and set an id, for example
 *  HtmlElement::addAttribute('id','mainelement');
 *  // set a list element with content as string
 *  HtmlElement::addElement('li', 'list 1');
 *  // if you need attributes for your setted element then first define the element, set the attributes and after that
 *  // pass the content.
 *  // Example: Arrays are also supported for content values.
 *  HtmlElement::addElement('li');
 *  HtmlElement::addAttribute('class', 'from array');
 *  HtmlElement::addData($dataArray);
 *  // As result you get 3 <li> elements with same class and content from the array
 *  // Next example defines a list element with data list, data terms and data descriptions. Therefor we use method addParentElement();
 *  // This method logs the selected elements because the endtags must be set later.
 *  HtmlElement::addParentElement('li');
 *  HtmlElement::addAttribute('class', 'link_1');
 *  HtmlElement::addParentElement('dl');
 *  HtmlElement::addAttribute('class', 'datalist_1');
 *  // now the elements with start and endtags
 *  HtmlElement::addElement('dt', 'term');
 *  HtmlElement::addElement('dd', 'description');
 *  // finally set the endtags for all opened parent elements
 *  HtmlElement::closeParentElement('dl');
 *  HtmlElement::closeParentElement('li');
 *  // Repeat with next list elements
 *  HtmlElement::addParentElement('li');
 *  HtmlElement::addParentElement('dl');
 *  HtmlElement::addElement('dt', 'term2');
 *  HtmlElement::addElement('dd', 'description2');
 *  HtmlElement::closeParentElement('dl');
 *  HtmlElement::closeParentElement('li');
 *  $htmlList= HtmlElement::getHtmlElement();
 *  echo $htmlList;
 *  @endcode
 *  @par Example_2 Nested Div Elements using nesting mode
 *  @code 
 *  // Creating block elements with nested divs.
 *  // Example using nesting mode for html elements
 *  // Setting mode to true you are allowed to set the main element ('div' in this example) further times
 *  // Default false it is not possible to set the main element again
 *
 *  parent::HtmlElement ('div', 'class', 'pagewrap', true);
 *  // now we can nest a second div element with a paragaph.
 *  // Because of div is the parent of the paragraph element, we must tell the class using method addParentElement();
 *  HtmlElement::addParentElement('div');
 *  // We want to set an Id for the div element, for example
 *  HtmlElement::addAttribute('id', 'Paragraphs', 'div');
 *  // Define a paragrph
 *  HtmlElement::addElement('p', 'Hello World');
 *  // Nested div element must be closed !
 *  HtmlElement::closeParentElement('div');
 *  // Get the block element
 *  $htmlBlock = HtmlElement::getHtmlElement();
 *  echo $htmlBlock;
 *  @endcode
 *  @par Example_3 Hyperlinks
 *  @code
 *  parent::HtmlElement();
 *  HtmlElement::addElement('a');
 *  HtmlElement::addAttribute('href', 'http://www.admidio.org');
 *  HtmlElement::addData('Admidio Homepage');
 *  $hyperlink = HtmlElement::getHtmlElement();
 *  echo $hyperlink;
 *  @endcode
 *  @par Example_4 Form element
 *  @code
 *  // Create a form element
 *  parent::HtmlElement('form', 'name', 'testform');
 *  HtmlElement::addAttribute('action', 'test.php');
 *  HtmlElement::addAttribute('method', 'post');
 *  HtmlElement::addAttribute('enctype', 'text/html');
 *  // add an input field with label
 *  HtmlElement::addElement('input');
 *  HtmlElement::addAttribute('type', 'text');
 *  HtmlElement::addAttribute('name', 'input');
 *  HtmlElement::addHtml('Inputfield:');
 *  // pass a whitespace because element has no content
 *  HtmlElement::addData(' ', true); // true for self closing element (default: false)
 *  // add a checkbox
 *  HtmlElement::addElement('input');
 *  HtmlElement::addAttribute('type', 'checkbox');
 *  HtmlElement::addAttribute('name', 'checkbox');
 *  HtmlElement::addHtml('Checkbox:');
 *  // pass a whitespace because element has no content
 *  HtmlElement::addData(' ', true); // true for self closing element (default: false)
 *  // add a submit button
 *  HtmlElement::addElement('input');
 *  HtmlElement::addAttribute('type', 'submit');
 *  HtmlElement::addAttribute('value', 'submit');
 *  // pass a whitespace because element has no content
 *  HtmlElement::addData(' ', true);

 *  echo HtmlElement::getHtmlElement();
 *  @endcode
 */
/*****************************************************************************
 *
 *  Copyright    : (c) 2004 - 2013 The Admidio Team
 *  Author       : Thomas-RCV
 *  Homepage     : http://www.admidio.org
 *  License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

abstract class HtmlElement {

    protected   $arrParentElements;             ///< Array with opened child elements 
    protected   $currentElement;                ///< Internal pointer showing to actual element or child element
    protected   $currentElementAttributes;      ///< Attributes of the current element
    protected   $currentElementDataWritten;     ///< Flag if an element is added but the data is not added
    protected   $htmlString;                    ///< String with prepared html
    private     $mainElement;                   ///< String with main element as string
    private     $mainElementAttributes;         ///< String with attributes of the main element
    private     $nesting;                       ///< Flag enables nesting of main elements, e.g div blocks ( Default : false )
    protected   $parentFlag;                    ///< Flag for setted parent Element
    private     $mainElementWritten;              ///< Flag if the main element was written in the html string
    
    /**
     * Constructor initializing all class variables
     * 
     * @param $element The html element to be defined
     * @param $attribute The Attribute for the html element
     * @param $value Value of the attribute
     * @param $nesting Enables nesting of main elements ( Default: False )
     */
    public function __construct($element = '', $attribute = '', $value = '', $nesting = false)
    {        
        $this->mainElementAttributes = array();

        if($attribute != '')
        {
            $this->mainElementAttributes[$attribute] = $value;
        }
        
        if($nesting === false)
        {
            $this->nesting = false;
        }
        else
        {
            $this->nesting = true;
        }
        
        $this->mainElement               = $element;
        $this->currentElement            = $element;
        $this->currentElementAttributes  = array();
        $this->htmlString                = '';
        $this->parentFlag                = 0;
        $this->arrParentElements         = array();
        $this->mainElementWritten          = false;
        $this->currentElementDataWritten = true;
    }
    
    /** Add attributes to the selected element. If that attribute is already added 
     *  than the new value will be attached to the current value.
     *  @param $attribute Name of the html attribute
     *  @param $value Value of the attribute
     *  @param $element Optional the element for which the attribute should be set, 
     *                  if this is not the current element
     */
    public function addAttribute($attribute, $value, $element = null)
    {
        If($element === null)
        {
            $element = $this->currentElement;
        }

        if($element == $this->mainElement)
        {
            if(array_key_exists($attribute, $this->mainElementAttributes) == true)
            {
                $this->mainElementAttributes[$attribute] = $this->mainElementAttributes[$attribute].' '.$value;
            }
            else
            {
                $this->mainElementAttributes[$attribute] = $value;
            }
        }
        else
        {
            if(array_key_exists($attribute, $this->currentElementAttributes) == true)
            {
                $this->currentElementAttributes[$attribute] = $this->currentElementAttributes[$attribute].' '.$value;
            }
            else
            {
                $this->currentElementAttributes[$attribute] = $value;
            }
        }
    }
    
    /** Set attributes from associative array.
     *  @param $array An array that contains all attribute names as array key 
     *                and all attribute content as array value
     */
    protected function setAttributesFromArray($array)
    {
        if(is_array($array))
        {
            foreach($array as $key => $value)
            {
                $this->addAttribute($key, $value);
            }
        }
        return false;
    }    
    
    /**
     *  Add data to current element
     *
     *  @param $data Content for the element as string, or array
     *  @param $selfClosing Element has self closing tag ( default: false)
     */
    public function addData($data, $selfClosing = false)
    {
        $startTag = '';
        $endTag   = '';

        // Define needed tags
        if($selfClosing === false)
        {
            $startTag = '<' .$this->currentElement . $this->getElementAttributesString() . '>';
            $endTag   = '</' . $this->currentElement . '>';
        }
        else
        {
            $startTag = '<' .$this->currentElement . $this->getElementAttributesString();
            $endTag   = '/>';
        }
        // data is a string
        if(!is_array($data))
        {

            $this->htmlString .= $startTag . $data . $endTag;
        }
        else
        {
            $this->htmlString .= $this->readData($data);
        }
        $this->currentElementAttributes = array();
        // set flag that the data of the current element is written to html string
        $this->currentElementDataWritten = true;
    }

    /**
     * @par Add new child element.
     * This method defines the next child element to be written in the output string.
     * If a parent element was defined before, the syntax with all setted attributes is written first from internal buffer to the string.
     * After that, the new element is defined.
     * The method determines that the element has @b no @b own @b child @b elements and has a closing tag.
     * If you need a parent element like a \<div\> with some \<p\> elements, use method addParentElement(); instead and then add the paragraph elements.
     * If nesting mode is active you are allowed to set the main element called with object instance again. Dafault: false
     * 
     *
     * @param $childElement valid child tags for element object
     * @param $data content values can be passed as string, array, bidimensional Array and assoc. Array. ( Default: no data )
     * @param $selfClosing Element has self closing tag ( default: false)
     */
    public function addElement($childElement, $attribute = '', $value = '',  $data = '', $selfClosing = false)
    {
        // if previous current element was not written to html string and the same child element is set 
        // than this could be a call of parent class so do not reinitialize the current element
        if($this->currentElementDataWritten == true || $childElement != $this->currentElement)
        {
            $this->currentElementDataWritten = false;
            
            if($attribute != '' || $value != '')
            {
                $this->addAttribute($attribute, $value);
            }
            // check if parent element is set, then write first the tag and attributes for the previous element
            if($this->parentFlag == true)
            {
                // Main element attributes are set in own variable, so in nesting mode main element can be set again
                if($this->currentElement == $this->mainElement)
                {
                    $this->currentElementAttributes = $this->mainElementAttributes;
                }
                
                $this->htmlString .= '<' .$this->currentElement . $this->getElementAttributesString() . '>';
                $this->currentElement = $childElement;
                $this->currentElementAttributes = array();
                $this->parentFlag = false;
            }

            // If first child is set start writing the html beginning with main element and attributes
            if($this->currentElement == $this->mainElement && $this->mainElement != '' && $this->mainElementWritten == false)
            {
                $this->htmlString .= '<' .$this->mainElement.$this->getMainElementAttributesString().'>';
                $this->mainElementWritten = true;
            }

            // If nesting is enabled, main element can be set again
            if($childElement == $this->mainElement && $this->nesting === true)
            {
                // now set as current position
                $this->currentElement = $childElement;
                // clear attribute buffer
                $this->currentElementAttributes = array();
            }

            if($childElement != $this->mainElement)
            {
                // now set as current position
                $this->currentElement = $childElement;
                // clear attribute buffer
                $this->currentElementAttributes = array();
            }

            // add content if exists
            if($data != '')
            {
                $this->addData($data, $selfClosing);
            }
        }
    }
    
    /** Add any string to the html output. If the main element wasn't written to the 
     *  html string than this will be done before your string will be added.
     *  @param $string Text as string in current string position
     */
    public function addHtml($string = '')
    {
        // If first child is set start writing the html beginning with main element and attributes
        if($this->currentElement == $this->mainElement && $this->mainElement != '' && $this->mainElementWritten == false)
        {
            $this->htmlString .= '<' .$this->mainElement.$this->getMainElementAttributesString().'>';
            $this->mainElementWritten = true;
        }

        $this->htmlString .= $string;
    }

    /**
     * @par Add a parent element that has own childs.
     * This method is needed if an element can have several child elements and the closing tag must be set after own child elements.
     * It logs the setted element in an array. Each time you define a new parent element, the function checks the log array, if the element already was set.
     * If the current element already was defined, then the function determines that the still opened tag must be closed first until it can be set again.
     * The method closeParentElement(); is called automatically to close the previous element.
     * By default it is not allowed to define several elements from same type. If needed use option @b nesting @b mode @b true!
     *
     * @param $parentElement Parent element to be set
     * @param $attribute Attribute name
     * @param $value Value for the attribute
     */
    public function addParentElement($parentElement, $attribute = '', $value = '')
    {   
        // Only possible for child elements of the main element or nesting mode is active!
        if($this->currentElement != $this->mainElement || $this->nesting === true)
        {   
            // check if already parent element is set, then write first the tag and attributes for the previous element
            if($this->parentFlag == true)
            {
                $this->htmlString .= '<' .$this->currentElement . $this->getElementAttributesString() . '>';
                //$this->currentElementAttributes = array();
            }
            else
            {   
                // set Flag
                $this->parentFlag = true;
   
                if($this->currentElement == $this->mainElement && $this->nesting === true && $this->mainElementWritten == false)
                {
                    $this->htmlString .= '<' .$this->currentElement . $this->getMainElementAttributesString() . '>';
                    $this->mainElementAttributes = array();
                }
            }

            if(!in_array($parentElement, $this->arrParentElements))
            {
                // If currently not defined and element has own child elements then log in array to define endtags later
                $this->arrParentElements[] = $parentElement;
            }
            elseif($this->nesting === true)
            {
                // in nesting mode always log elements
                $this->arrParentElements[] = $parentElement;
            }
            else
            {
                // already set and we need the endtag first before setting again
                $this->closeParentElement($parentElement);
                $this->arrParentElements[] = $parentElement;
            }
            // set parent element to current element
            $this->currentElement = $parentElement;
			// initialize attributes because parent element should not get attributes of previous element
			$this->currentElementAttributes = array();

            // save attribute for parent element
            if($attribute != '' || $value != '')
            {
                $this->addAttribute($attribute, $value);
            }
            //$this->mainElementAttributes = array();
        }
    }
    
    /**
     * @par Close parent element.
     * This method sets the endtag of the selected element and removes the entry from log array.
     * If nesting mode is not used, the methods looks for the entry in the array and determines that all setted elements after the selected element must be closed as well.
     * All end tags to position are closed automatically starting with last setted element tag. 
     *
     * @param $parentElement Parent element to be closed
     */
    public function closeParentElement($parentElement)
    {
        // intialize position and count entries in array
        $position = '';
        $totalCount = count($this->arrParentElements);

        if($totalCount == 0 )
        {
            return false;
        }

        if(in_array($parentElement, $this->arrParentElements) && $this->nesting === false)
        {
            // find position in log array
            for($i = 0; $i < $totalCount-1; $i++)
            {
                if($this->arrParentElements[$i] == $parentElement)
                {
                    $position = $i;
                }
            }

            // if last position set Endtag in string and remove from array
            if($position == $totalCount)
            {
                $this->htmlString .= '</' . $this->arrParentElements[$totalCount] . '>';
                unset($this->arrParentElements[$position]);
            }
            else
            {
                // all elements setted later must also be closed and removed from array
                for($i = $totalCount-1; $i >= $position; $i--)
                {
                    $this->htmlString .= '</' . $this->arrParentElements[$i] . '>';
                    unset($this->arrParentElements[$i]);
                }
            }
        }
        else
        {
            // close last tag and delete whitespaces in log array
            $this->htmlString .= '</' . $this->arrParentElements[$totalCount-1] . '>';
            unset($this->arrParentElements[$totalCount-1]);
        }
        $this->arrParentElements = array_values($this->arrParentElements);
    }
    
    /** Create a valid html compatible string with all attributes and their values of the 
     *  last added element.
     *  @return Returns a string with all attributes and values.
     */
    private function getElementAttributesString()
    {
        $string = '';
        
        foreach($this->currentElementAttributes as $key => $value)
        {
            $string .= ' '.$key.'="'.$value.'" ';
        }
        
        return $string;
    }

    
    /** Create a valid html compatible string with all attributes and their values of the 
     *  main element.
     *  @return Returns a string with all attributes and values.
     */
    private function getMainElementAttributesString()
    {
        $string = '';
        
        foreach($this->mainElementAttributes as $key => $value)
        {
            $string .= ' '.$key.'="'.$value.'" ';
        }
        
        return $string;
    }

    /**
     * Return the element as string
     *
     * @return Returns the parsed html as string
     */
    public function getHtmlElement()
    {
        $this->htmlString .= '</' . $this->mainElement . '>';
        
        return  $this->htmlString;
    }
    
    /**
     *  Prepare html of data added from content arrays
     *  @param: $data Array with content for child elements
     *  @param $selfClosing Element has self closing tag ( default: false)
     *  @return Returns FALSE is no data is given
     */
    private function readData($data, $selfClosing = false)
    {
        $startTag = '';
        $endTag   = '';
        
        if(isset($data)) 
        {
            $count = 0; 
            // no selfclosing element
            if($selfClosing === false)
            {
                $startTag = '<' .$this->currentElement . $this->getElementAttributesString() . '>';
                $endTag   = '</' . $this->currentElement . '>';
            }
            else
            {
                $startTag = '<' .$this->currentElement . $this->getElementAttributesString();
                $endTag   = '/>';
            }
            // count entries
            $numberEntries = count($data); 
            // count 1 level deeper.
            $nextLevel = count($data[0]);
            if($nextLevel > 1) 
            {
                // bidimensional or assoc. array
                for ($i = 0; $i < count($data); $i++) 
                {

                    foreach ($data[$i] as $col => $value) 
                    {
                        $this->htmlString .= $startTag . $value . $endTag;
                    } 
                } 
            } 
            else 
            {
                // single array
                foreach ($data as $col) 
                {
                    $this->htmlString .= $startTag . $col . $endTag;
                } 
            } 
        } 
        return false;
    } 
} 
?>
