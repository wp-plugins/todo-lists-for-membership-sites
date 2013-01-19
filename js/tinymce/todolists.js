(function() {

	var ajaxdata = {
		action: "todolists",
	};

	jQuery.get(window.todolists_ajaxurl, ajaxdata, function(data)
	{
		jQuery(data).appendTo("body").hide();
		jQuery(".todolists_listid").click(function()
		{
			var listid = jQuery(this).attr("id");
			tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[' + window.todolists_currentshortcode + ' id="' + listid + '"]');
			tb_remove();
		});

	});

    tinymce.create('tinymce.plugins.TodoLists', {
        init : function(ed, url) {
            ed.addButton('todolists-tasklist', {
                title : 'Todo Lists Tasks',
                image : url+'/todolists-tasklist.png',
                onclick : function() {

					window.todolists_currentshortcode = "todolists_tasklist";
					tb_show('Todo Lists', '#TB_inline?width=400&height=200&inlineId=todolists' );

                }
            });
			ed.addButton('todolists-progressbar', {
                title : 'Todo Lists Progress Bar',
                image : url+'/todolists-progressbar.png',
                onclick : function() {
					
					window.todolists_currentshortcode = "todolists_progressbar";
					tb_show('Todo Lists', '#TB_inline?width=400&height=200&inlineId=todolists' );

                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname : "TodoLists",
                author : 'Manfred Ekblad',
                authorurl : 'http://manfredekblad.net/',
                infourl : 'http://manfredekblad.net/',
                version : "1.0"
            };
        }
    });
    tinymce.PluginManager.add('todolists', tinymce.plugins.TodoLists);
})();