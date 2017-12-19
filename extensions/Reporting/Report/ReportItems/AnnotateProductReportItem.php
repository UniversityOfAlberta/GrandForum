<?php

class AnnotateProductReportItem extends AbstractReportItem {
    
    function render(){
        global $wgOut;
        $product = Product::newFromId($this->productId);
        
        
        $html = "<span id='{$this->getPostId()}_span'>{$product->getCitation(true, false, false, false, $this->personId)}</span>";
        $html .= "<textarea id='{$this->getPostId()}' name='{$this->getPostId()}' style='display:none;'>{$this->getBlobValue()}</textarea>";
        $html .= "<div id='{$this->getPostId()}_dialog' title='Author Classification' style='display:none;'></div>";
        $html .= "<script id='{$this->getPostId()}_template' type='text/template'>
            <table>
                <tr>
                    <td align='right' style='white-space:nowrap;'><b>Name:</b></td>
                    <td>
                        <span id='{$this->getPostId()}_name'><%= name %></span>
                    </td>
                </tr>
                <tr>
                    <td align='right' style='white-space:nowrap;'><b>Type of User:</b></td>
                    <td>
                        <select name='type'>
                            <option <% if(type == ''){ %>selected <% } %>></option>
                            <option <% if(type == 'Undergraduate Student'){ %>selected <% } %>>Undergraduate Student</option>
                            <option <% if(type == 'Graduate Student'){ %>selected <% } %>>Graduate Student</option>
                            <option <% if(type == 'Postdoctoral Student'){ %>selected <% } %>>Postdoctoral Student</option>
                            <option <% if(type == 'Faculty'){ %>selected <% } %>>Faculty</option>
                            <option <% if(type == 'None of the Above'){ %>selected <% } %>>None of the Above</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align='right' style='white-space:nowrap;'><b>Email:</b></td>
                    <td>
                        <input type='text' name='email' value='<%= email %>' />
                    </td>
                </tr>
            </table>
        </script>";
        $html .= "<script type='text/javascript'>
            $('#{$this->getPostId()}_span span.citation_author').css('padding', '3');
            $('#{$this->getPostId()}_span span.citation_author').css('border-radius', '3px');
            $('#{$this->getPostId()}_span span.citation_author').css('cursor', 'pointer');
            
            $('#{$this->getPostId()}_span span.citation_author').mouseover(function(){
                $(this).css('background', highlightColor);
                $(this).css('color', '#FFFFFF');
            });
            
            $('#{$this->getPostId()}_span span.citation_author').mouseout(function(){
                $(this).css('background', '');
                $(this).css('color', '');
            });
            
            var str = $('textarea[name={$this->getPostId()}]').val();
            var {$this->getPostId()}_data = {}
            if(str != ''){
                {$this->getPostId()}_data = JSON.parse(str);
            }
            
            var {$this->getPostId()}_render = function(){
                $('#{$this->getPostId()}_span span.citation_author').each(function(i, el){
                    var name = $(el).text();
                    var obj = {$this->getPostId()}_data[name];
                    var type = (obj != undefined) ? obj.type : '';
                    switch(type){
                        case 'Undergraduate Student':
                            $(el).css('text-decoration', 'underline');
                            $(el).css('font-weight', 'normal');
                            break;
                        case 'Postdoctoral Student':
                        case 'Graduate Student':
                            $(el).css('font-weight', 'bold');
                            $(el).css('text-decoration', 'none');
                            break;
                        case 'Faculty':
                        case 'None of the Above':
                            $(el).css('text-decoration', 'none');
                            $(el).css('font-weight', 'normal');
                            break;
                        case '':
                            $(el)[0].style.textDecoration = $(el)[0].oldStyle.textDecoration;
                            $(el)[0].style.fontWeight = $(el)[0].oldStyle.fontWeight;
                            break;
                    }
                });
            };
            
            $('#{$this->getPostId()}_span span.citation_author').each(function(i, el){
                $(el)[0].oldStyle = _.clone($(el)[0].style);
            });
            {$this->getPostId()}_render();
            
            $('#{$this->getPostId()}_dialog').dialog({
                autoOpen: false,
                width: 'auto',
                buttons: {
                    Save: function(){
                        {$this->getPostId()}_data[$('#{$this->getPostId()}_name').text()] = {
                            name: $('#{$this->getPostId()}_name').text(),
                            type: $('select[name=type]', $(this)).val(),
                            email: $('input[name=email]', $(this)).val().replace(/'/g, '&#39;')
                        };
                        $('textarea[name={$this->getPostId()}]').val(JSON.stringify({$this->getPostId()}_data));
                        {$this->getPostId()}_render();
                        saveAll();
                        $(this).dialog('close');
                    },
                    Cancel: function(){
                        $(this).dialog('close');
                    }
                }
            });
            
            $('#{$this->getPostId()}_span span.citation_author').click(function(){
                var name = $(this).text();
                var obj = {$this->getPostId()}_data[name];
                var type = (obj != undefined) ? obj.type : '';
                var email = (obj != undefined) ? obj.email : '';
                var template = _.template($('#{$this->getPostId()}_template').html());
                $('#{$this->getPostId()}_dialog').html(template({name: name, type: type, email: email}));
                $('#{$this->getPostId()}_dialog').dialog('open');
            });
        </script>";
        
        $item = $this->processCData($html);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $product = Product::newFromId($this->productId);
        
        $html = $product->getCitation(true, false, false, false, $this->personId);
        $data = (array)json_decode($this->getBlobValue());

        $dom = new SmartDomDocument();
        $dom->loadHTML($html);
        $spans = $dom->getElementsByTagName("span");
        foreach($spans as $span){
            if($span->getAttribute('class') == 'citation_author'){
                $name = $span->nodeValue;
                if(isset($data[$name])){
                    switch($data[$name]->type){
                        case 'Undergraduate Student':
                            $span->setAttribute('style', 'text-decoration: underline !important;');
                            break;
                        case 'Postdoctoral Student':
                        case 'Graduate Student':
                            $span->setAttribute('style', 'font-weight: bold !important;');
                            break;
                        case 'Faculty':
                        case 'None of the Above':
                            $span->setAttribute('style', 'font-weight: normal !important; text-decoration: none !important;');
                            break;
                    }
                }
            }
        }
        
        $item = $this->processCData("$dom");
        $wgOut->addHTML($item);
    }

}

?>
