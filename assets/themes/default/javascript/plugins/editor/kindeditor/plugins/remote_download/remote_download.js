KindEditor.plugin('remote_download', function(K) {
        var editor = this, name = 'remote_download'; 
        editor.clickToolbar(name, function() {
				K.ajax(SITE_URL+'swfupload/remote_download', function(data) {
						//console.log(data);
						editor.html(data);
				}, 'POST', {
						datas: editor.html()
				});  
        });
});