$(function() {
	var url = window.location.href;
	var reg= /\?(.*)$/;
	var res= reg.exec(url);
	var strCookie=document.cookie; 
	var arrCookie=strCookie.split(";"); 
	var userId; 
	for(var i=0;i<arrCookie.length;i++){ 
		var arr=arrCookie[i].split("="); 
		if("YID"==arr[0]){ 
		userId=arr[1]; 
		break; 
		} 
		}  
	$.ajax({
		type: 'GET',
		url: 'http://yedaolog.sinaapp.com/10buck/'+res[0]+'&u='+userId,
		dataType: 'text',
		success: function(data){
			
		}
	});
});