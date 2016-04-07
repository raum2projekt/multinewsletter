function myrex_confirm(text,form) {
	if(text !== undefined && form !== undefined) {
		check = confirm(text);
		if(check) {
			return true;
		}
		else {
			return false;
		}
	}
}
  
function myrex_selectallitems(fieldname,inputfield) {
	if(typeof(inputfield) !== "undefined" && typeof(fieldname) !== "undefined") {
		form = inputfield.form;
		if(typeof(form[fieldname+'_all']) !== "undefined") {
			if(inputfield === form[fieldname+'_all'] && form[fieldname+'_all'].checked === true) {
				// inputfield.form[fieldname+'_all'].checked = true;
				for(i=0; i<form.elements.length; i++) {
					if(form.elements[i].name.indexOf(fieldname+'[')>-1) {
						form.elements[i].checked=true;
					}
				}
			}
			else if(inputfield === form[fieldname+'_all'] && form[fieldname+'_all'].checked === false) {
				// inputfield.form[fieldname+'_all'].checked = true;
				for(i=0; i<form.elements.length; i++) {
					if(form.elements[i].name.indexOf(fieldname+'[')>-1) {
						form.elements[i].checked=false;
					}
				}
			}
			else {
				allselected = true;
				for(i=0; i<form.elements.length; i++) {
					if(form.elements[i].name.indexOf(fieldname+'[')>-1 && form.elements[i].checked === false) {
						allselected = false;
					}
				}
				if(allselected === true) {
					form[fieldname+'_all'].checked = true;
				}
				else {
					form[fieldname+'_all'].checked = false;
				}
			}
		}
	}
}