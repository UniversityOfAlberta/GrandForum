<?php

class PlusMinus extends UIElementArray {
    
    function FieldSet($id){
        parent::UIElementArray($id);
    }
    
    function render(){
        $hiddenHTML = "<div class='{$this->id}_contents_template'>
                            <div class='{$this->id}_contents'>";
        foreach($this->elements as $element){
            $hiddenHTML .= $element->render();
        }
        $hiddenHTML .= "</div></div>";
        $html = $hiddenHTML;
        $html .= "<div id='{$this->id}'>
            <div class='plusminus_contents'></div>
            <a id='{$this->id}add' class='button' style='width: 20px;padding-left: 5px;padding-right: 5px;'>+</a>&nbsp;
            <a id='{$this->id}minus' class='button' style='width: 20px;padding-left: 5px;padding-right: 5px;'>-</a>
        </div>";
        $html .= "<script type='text/javascript'>
            var contents = $('.{$this->id}_contents_template').detach();
            $('#{$this->id} .plusminus_contents').append(contents.html());
            $('#{$this->id}add').click(function(){
                $('#{$this->id} .plusminus_contents').append(contents.html());
            });
            $('#{$this->id}minus').click(function(){
                $('#{$this->id} .plusminus_contents .{$this->id}_contents').last().remove();
            });
        </script>";
        return $html;
    }
}

?>
