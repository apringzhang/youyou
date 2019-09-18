(function(){
	var iWidth=document.documentElement.getBoundingClientRect().width;iWidth=iWidth>750?750:iWidth;
	document.getElementsByTagName("html")[0].style.fontSize=iWidth/10/0.75+"px";
})();
window.onresize = function(){
	var iWidth=document.documentElement.getBoundingClientRect().width;iWidth=iWidth>750?750:iWidth;
	document.getElementsByTagName("html")[0].style.fontSize=iWidth/10/0.75+"px";
};
