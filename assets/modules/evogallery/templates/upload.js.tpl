<script type="text/javascript" charset="utf-8">
<!--
$(document).ready(function(){
	$("#uploadify").uploadify({
		'uploader': '[+base_path+]js/uploadify/uploadify.swf',
		'script': '[+base_path+]upload.php',
		'checkScript': '[+base_path+]check.php',
		'scriptData': {[+params+]},
		'folder': '[+base_url+]assets/galleries/[+content_id+]',
		'multi': true,
		'fileDesc': '[+lang.image_files+]',
		'fileExt': '*.jpg;*.png;*.gif',
		'simUploadLimit': 2,
		'sizeLimit': [+upload_maxsize+],
		'buttonText': '[+lang.select_files+]',
		'cancelImg': '[+base_path+]js/uploadify/cancel.png',
		'onComplete': function(event, queueID, fileObj, response, data) {
            var uploadList = $('#uploadList');
            uploadList.append("<li><div class=\"thbButtons\"><a href=\"" + unescape('[+self+]') + "&action=edit&content_id=[+content_id+]&edit=" + escape(response) + "\" class=\"edit\">[+lang.edit+]</a><a href=\"" + unescape('[+self+]') + "&delete=" + escape(response) + "\" class=\"delete\">[+lang.delete+]</a></div><img src=\"" + unescape('[+thumbs+]') + "&filename=" + escape(response) + "\" alt=\"" + response + "\" class=\"thb\" /><input type=\"hidden\" name=\"sort[]\" value=\"" + escape(response) + "\" /></li>");
        },
        'onAllComplete': function(){
            $(".thbButtons").hide();
        }
	});
    $('#uploadFiles').click(function(){
        $('#uploadify').uploadifyUpload();
        return false;
    });
    $('#clearQueue').click(function(){
        $('#uploadify').uploadifyClearQueue();
        return false;
    });
	if($('#uploadList').length > 0){
        $(".thbButtons").hide();
        $("#uploadList li").live("mouseover", function(){
                $(this).find(".thbButtons").show();
        });
        $("#uploadList li").live("mouseout", function(){
                $(this).find(".thbButtons").hide();
        });
        $(".thbButtons .delete").live("click", function(event){
            if(confirm('[+lang.delete_confirm+]')){
                $.get($(this).attr('href'));
                $(this).parent().parent('li').remove();            
            }
            return false;
        });
        $(".edit").live("click", function(event){
            var link = $(this).attr("href");
            var overlay = $(this).overlay({
                api: 'true',
                target: '#overlay',
                oneInstance: true,
                onBeforeLoad: function(){
                    $("#overlay .contentWrap").load(link, function(){
                        var keyword_tags = new TagCompleter("keywords", "keyword_tagList", ",");
                    });
                },
                onClose: function(){
                    if($('.newimage').length > 0){
                        window.location.reload();
                    }
                },
                onLoad: function(){
                    $("#cmdsave").click(function(){
                        overlay.close();
                    });
                    $.urlParam = function(name){
                    	var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(link);
                    	return results[1] || 0;
                    }
                	$("#newimage").uploadify({
                		'uploader': '[+base_path+]js/uploadify/uploadify.swf',
                		'script': '[+base_path+]replace.php',
                		'checkScript': '[+base_path+]check.php',
                		'scriptData': {[+params+], 'edit': $.urlParam('edit')},
                		'folder': '[+base_url+]assets/galleries/[+content_id+]',
                		'multi': false,
                		'fileDesc': '[+lang.image_files+]',
                		'fileExt': '*.jpg;*.png;*.gif',
                		'simUploadLimit': 2,
                		'sizeLimit': [+upload_maxsize+],
						'buttonText': '[+lang.browse_file+]',
                   		'cancelImg': '[+base_path+]js/uploadify/cancel.png',
                		'onComplete': function(event, queueID, fileObj, response, data) {
                            $('.thumbPreview').empty().append('<img class="newimage" src="' + unescape('[+thumbs+]') + '&filename=' + escape(response) + '" alt="' + response + '" />');

                        }
               	    });
                    $('#newimageupload').click(function(){
                        $('#newimage').uploadifyUpload();
                        return false;
                    });
                }
            });
            overlay.load();
            return false;
        });

        $("#uploadList").sortable();
	}
});
-->
</script>
