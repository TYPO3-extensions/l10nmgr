var exporter = {

 	init: function(){
		exporter.runExport();
	},
	
	runExport: function(){
		
		$.getJSON(tx_l10nmgr_ajaxDoExportRunUrl,
			function(json){
				if(json.progressValue < 100){
					
					$('.mvc_progress_bar').css("width",json.progressValue+"%");
					$('.mvc_progress_label').html(json.progressLabel);
					exporter.runExport();
				}else{
					$('.mvc_progress_bar').css("width",100+"%");
					$('.mvc_progress_label').html(json.progressLabel);
				}
			}
		);		
	}
}

$(document).ready(function() {
	exporter.init();
});