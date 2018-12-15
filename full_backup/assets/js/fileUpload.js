///////////////////////////////////////////////////////////////////////////////////////
//
// Project Name  	- 	BBH Dashboard
// Company			-	Aroxo Software Private Limited
// Module			- 	Drag and Drop File Upload
// Author(s)		-	Manibharathi P
// Date				-	23 May 2014
// Description		-	Drag and Drop File Upload
//
///////////////////////////////////////////////////////////////////////////////////////

$(document).ready(function() {
	$(document).on('dragenter', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
	$(document).on('dragover', function (e) 
	{
	  e.stopPropagation();
	  e.preventDefault();
	});
	$(document).on('drop', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
});
function fileDragDrop(obj,uploadUrl){
	/*obj.on('dragenter', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
		obj.css('border', '2px solid #0B85A1');
	});
	obj.on('dragover', function (e) 
	{
		 //e.stopPropagation();
		 //e.preventDefault();
		 obj.css('border', '2px dotted #0B85A1');
	});
	obj.on('dragleave', function (e) 
	{
		obj.css('border', '2px dashed #92AAB0');
	});
	obj.on('drop', function (e) 
	{
		 obj.css('border', '2px dashed #92AAB0');
		 e.preventDefault();
		 var files = e.originalEvent.dataTransfer.files;
		 handleFileUpload(files,obj,uploadUrl);
	});*/
	obj.on('click',function(e)
	{
		obj.after('<input type="file" name="file" id="file" style="display:none" />');
		obj.next().trigger('click');
		obj.next().change(function(){handleFileUpload($(this)[0].files,obj,uploadUrl)});
	});
}
function sendFileToServer(formData,status,uploadUrl)
{
	var uploadURL = uploadUrl; //Upload URL
	var extraData ={}; //Extra Data.
	var jqXHR=$.ajax({
			xhr: function() {
			var xhrobj = $.ajaxSettings.xhr();
			if (xhrobj.upload) {
					xhrobj.upload.addEventListener('progress', function(event) {
						var percent = 0;
						var position = event.loaded || event.position;
						var total = event.total;
						if (event.lengthComputable) {
							percent = Math.ceil(position / total * 100);
						}
						//Set progress
						status.setProgress(percent);
					}, false);
				}
			return xhrobj;
		},
		url: uploadURL,
		type: "POST",
		contentType:false,
		processData: false,
		cache: false,
		data: formData,
		success: function(data){
			status.setProgress(100);
		}
	}).done(function(data, status, xhr) {
		fileUploadCallBack(data);
	});
	status.setAbort(jqXHR);
}
function createStatusbar(obj)
{
	 this.statusbar = $("<div class='progress upload-bar progress-striped progress-success active'></div>");
	 this.filename = $("<div class='filename' style='display:none'></div>").appendTo(this.statusbar);
	 this.size = $("<div class='filesize' style='display:none'></div>").appendTo(this.statusbar);
	 this.progressBar = $("<div class='bar'></div>").appendTo(this.statusbar);
	 this.abort = $("<div class='abort' style='display:none'>Abort</div>").appendTo(this.statusbar);
	 obj.after(this.statusbar);
	
	this.setFileNameSize = function(name,size)
	{
		var sizeStr="";
		var sizeKB = size/1024;
		if(parseInt(sizeKB) > 1024)
		{
			var sizeMB = sizeKB/1024;
			sizeStr = sizeMB.toFixed(2)+" MB";
		}
		else
		{
			sizeStr = sizeKB.toFixed(2)+" KB";
		}
	
		this.filename.html(name);
		this.size.html(sizeStr);
	}
	this.setProgress = function(progress)
	{		
		var progressBarWidth =progress*this.statusbar.width()/ 100;  
		this.progressBar.animate({ width: progressBarWidth }, 10).html(progress + "%&nbsp;");
		if(parseInt(progress) >= 100)
		{
			setTimeout(function(){$('.progress').remove()},1000);
			this.abort.hide();
		}
	}
	this.setAbort = function(jqxhr)
	{
		var sb = this.statusbar;
		this.abort.click(function()
		{
			jqxhr.abort();
			sb.hide();
		});
	}
}
function handleFileUpload(files,obj,uploadUrl)
{
   for (var i = 0; i < files.length; i++) 
   {
		var fd = new FormData();
		fd.append('file', files[i]);

		window.uploadedFileName = files[i].name.substring(0,20) + "...";
		window.uploadedTime = displayTime();
		var status = new createStatusbar(obj); //Using this we can set progress.
		status.setFileNameSize(files[i].name,files[i].size);
		sendFileToServer(fd,status,uploadUrl);
   }
}
function displayTime() {
    var str = "";

    var currentTime = new Date()
	var year = currentTime.getFullYear()
	var month = currentTime.getMonth()
	var date = currentTime.getDate()
    var hours = currentTime.getHours()
    var minutes = currentTime.getMinutes()
    var seconds = currentTime.getSeconds()

    if (minutes < 10) {
        minutes = "0" + minutes
    }
    if (seconds < 10) {
        seconds = "0" + seconds
    }
    str += date+'/'+(month+1)+'/'+year+' '+hours + ":" + minutes;
    return str;
}