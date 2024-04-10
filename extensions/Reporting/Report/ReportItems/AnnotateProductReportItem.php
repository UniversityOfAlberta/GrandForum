<?php

class AnnotateProductReportItem extends AbstractReportItem {
    
    function render(){
        global $wgOut, $config;
        $product = Product::newFromId($this->productId);
        $showStatus = (strtolower($this->getAttr("showStatus", "false") == "true"));
        
        //Sanity Check: If there is ONLY title and year (no data), set incomplete == true;
        $incomplete = true;
        $peerReviewedMissing = false;
        $impactFactorMissing = false;
        $snipMissing = false;
        $acceptanceDateMissing = false;
        $structure = $product->getStructure();
        if(count($structure['data']) == 0){
            // Type has no data fields
            $incomplete = false;
        }
        else{
            foreach($structure['data'] as $key => $val){
                if($product->getData($key) != ""){
                    // At least one data field has been entered
                    $incomplete = false;
                    break;
                }
            }
        }

        if($product->getCategory() == "Publication" &&
           $product->getData('peer_reviewed') == ""){
            $peerReviewedMissing = true;  
        }
        
        if($config->getValue('elsevierApi') != ""){
            if($product->getCategory() == "Publication" &&
               isset($structure['data']['snip']) &&
               $product->getData('snip') == ""){
                $snipMissing = true;
            }
        }
        else{
            if($product->getCategory() == "Publication" &&
               isset($structure['data']['impact_factor']) &&
               $product->getData('impact_factor') == "" &&
               $product->getData('impact_factor_override') == ""){
                $impactFactorMissing = true;
            }
        }
        $structure = Product::structure();
        $acceptanceDateLabel = @$structure['categories'][$product->getCategory()]['types'][$product->getType()]["acceptance_date_label"];
        if($product->getCategory() == "Publication" &&
           $acceptanceDateLabel == "Acceptance Date" &&
           ($product->getAcceptanceDate() == "0000-00-00" || $product->getAcceptanceDate() == "")){
            $acceptanceDateMissing = true;
        }
        $html = "";
        if($incomplete || $peerReviewedMissing || $impactFactorMissing || $snipMissing){
            $html .= "<span style='background:orange;'>";
        }
        else{
            $html .= "<span>";
        }
        $html .= "<span id='{$this->getPostId()}_span'>{$product->getCitation(true, $showStatus, false, false, $this->personId)}</span>";
        if($incomplete || $peerReviewedMissing || $impactFactorMissing || $snipMissing || $acceptanceDateMissing){
            $html .= "<ul style='color: #FF6600;'>";
            if($incomplete){
                $html .= "<li>This entry may be incomplete</li>";
            }
            if($peerReviewedMissing){
                $html .= "<li>This entry is missing a Peer Reviewed status</li>";
            }
            if($impactFactorMissing){
                $html .= "<li>This entry is missing Impact Factor information</li>";
            }
            if($snipMissing){
                $html .= "<li>This entry is missing SNIP information</li>";
            }
            if($acceptanceDateMissing){
                $html .= "<li>This entry is missing an Acceptance Date</li>";
            }
            $html .= "</ul>";
        }
        $html .= "</span>";
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
            $('#{$this->getPostId()}_span span.citation_author').css('padding', '1px 3px');
            $('#{$this->getPostId()}_span span.citation_author').css('border-radius', '3px');
            $('#{$this->getPostId()}_span span.citation_author').css('cursor', 'pointer');
            
            $('#{$this->getPostId()}_span span.citation_author').mouseover(function(){
                $(this).css('background', highlightColor);
                $(this).css('color', '#FFFFFF');
            });
            
            $('#{$this->getPostId()}_span span.citation_author').mouseout(function(){
                $(this).css('background', '');
                $(this).css('color', '');
                if($(this).hasClass('faculty_author')){
                    $(this).css('background', '#dfdfdf');
                }
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
                            $(el).css('background', '');
                            $(el).css('font-style', 'normal');
                            $(el).css('text-decoration', 'underline');
                            $(el).css('font-weight', 'normal');
                            $(el).removeClass('faculty_author');
                            break;
                        case 'Postdoctoral Student':
                            $(el).css('background', '');
                            $(el).css('font-style', 'italic');
                            $(el).css('font-weight', 'normal');
                            $(el).css('text-decoration', 'none');
                            $(el).removeClass('faculty_author');
                            break;
                        case 'Graduate Student':
                            $(el).css('background', '');
                            $(el).css('font-style', 'normal');
                            $(el).css('font-weight', 'bold');
                            $(el).css('text-decoration', 'none');
                            $(el).removeClass('faculty_author');
                            break;
                        case 'Faculty':
                            $(el).css('background', '#dfdfdf');
                            $(el).css('font-style', 'normal');
                            $(el).css('text-decoration', 'none');
                            $(el).css('font-weight', 'normal');
                            $(el).addClass('faculty_author');
                            break;
                        case 'None of the Above':
                            $(el).css('background', '');
                            $(el).css('font-style', 'normal');
                            $(el).css('text-decoration', 'none');
                            $(el).css('font-weight', 'normal');
                            $(el).removeClass('faculty_author');
                            break;
                        case '':
                            $(el)[0].style.textDecoration = $(el)[0].oldStyle.textDecoration;
                            $(el)[0].style.fontWeight = $(el)[0].oldStyle.fontWeight;
                            $(el)[0].style.fontStyle = $(el)[0].oldStyle.fontStyle;
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
        $showStatus = (strtolower($this->getAttr("showStatus", "false") == "true"));
        $html = $product->getCitation(true, $showStatus, false, false, $this->personId);
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
                            $span->setAttribute('style', 'font-style: italic !important;');
                            break;
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
