<?php

class Helper_Element_View_SelectGenre extends Zend_Dojo_View_Helper_Dijit
{
     /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.Textarea';

    /**
     * HTML element type
     * @var string
     */
    protected $_elementType = 'text';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.Textarea';

    /**
     * dijit.form.Textarea
     * 
     * @param  int $id 
     * @param  mixed $value 
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function SelectGenre($id, $value = null, array $params = array(), array $attribs = array())
    {

        $this->view->dojo()->requireModule('dijit.Dialog');
        $this->view->dojo()->requireModule('dojo.data.ItemFileReadStore');
        $this->view->dojo()->requireModule('dijit.Tree');
        $this->view->dojo()->requireModule('dojo.parser');
        $this->view->dojo()->requireModule('dijit.form.Button');


        
        
        $valueArray = unserialize($value);

        $newValue = '';
        $newIds   = '';
        foreach($valueArray as $values)
        {
            $newValue .= '
            <div dojoType="dijit.Dialog" id="dialog_'.$values['genre_id'].'" title="Remove Genre  ">
                Are you sure you want to remove the genre <i>'.$values['genre_name'] .'</i> from this Artist?<br>
                <center><button dojoType="dijit.form.Button" onclick="document.getElementById(\'value_'.$values['genre_id'].'\').innerHTML = \'\'; dijit.byId(\'dialog_'.$values['genre_id'].'\').hide();">Ok</button></center> 
           </div>';

            $newValue .= '
            <div id="value_'.$values['genre_id'].'">
                <a href="javascript:dijit.byId(\'dialog_'.$values['genre_id'].'\').show(); dijit.byId(\'dialog_'.$values['genre_id'].'\').Text(\''.$values['genre_name'] . '\')">'.$values['genre_name'] . '</a>
                <input type="hidden" name="genre['.$values['genre_id'].']" id="genre['.$values['genre_id'].']" value="'.$values['genre_id'].'">
                <br> ';
            $newValue .= '</div>';                     
        }

        $html = '
        <div class="grid_16">
            
            <button dojoType="dijit.form.Button" onclick="dijit.byId(\'dialog1\').show()">Chose Genre</button>
            
            <div dojoType="dijit.Dialog" id="dialog1" title="Chose Genre" >
                   

                <div dojoType="dojo.data.ItemFileReadStore" jsId="genreStore" url="genre/json/getgenre"></div>

                
                <div dojoType="dijit.Tree" id="tree1" store="genreStore" query="{type:\'top\'}" labelAttr="name" label="Top Level">
                  <script type="dojo/method" event="onClick" args="item">
                    if(item){                     
                       var html = document.getElementById(\'container\').innerHTML;
                    
                       var newString =\'<div id="value_\' + genreStore.getValue(item, "id") + \'"><b>\' + genreStore.getValue(item, "name") + \'</b><input type="hidden" name="genre[\'+genreStore.getValue(item, "id")+\']" id="genre[\'+genreStore.getValue(item, "id")+\']" value="\'+genreStore.getValue(item, "id")+\'"></div>\';


                       document.getElementById(\'container\').innerHTML =  newString + html;                        
                    }else{
                      alert("Execute on root node");
                    }
                  </script>
                </div>
                
                 <button dojoType="dijit.form.Button" onclick="dijit.byId(\'dialog1\').hide()">Done</button>
            </div>

            

        </div>
        <div id="container">'.$newValue.'

        </div>
       
        ';

     

        

        return $html;
    }

}
