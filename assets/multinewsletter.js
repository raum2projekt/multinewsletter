  function myrex_confirm(text,form) {
    if(text!=undefined && form!=undefined)
    {
      check = confirm(text);
      if(check)
      {
        return true;
      }
      else
      {
        return false;
      }
    }
  }
  
  function myrex_selectallitems(fieldname,inputfield)
  {
    if(typeof(inputfield) != "undefined" && typeof(fieldname) != "undefined")
    {
      form = inputfield.form;
      if(typeof(form[fieldname+'_all']) != "undefined")
      {
        if(inputfield == form[fieldname+'_all'] && form[fieldname+'_all'].checked==true)
        {
          // inputfield.form[fieldname+'_all'].checked = true;

          for(i=0; i<form.elements.length; i++)
            if(form.elements[i].name.indexOf(fieldname+'[')>-1)
              form.elements[i].checked=true;

        }
        else if(inputfield == form[fieldname+'_all'] && form[fieldname+'_all'].checked==false)
        {
          // inputfield.form[fieldname+'_all'].checked = true;

          for(i=0; i<form.elements.length; i++)
            if(form.elements[i].name.indexOf(fieldname+'[')>-1)
              form.elements[i].checked=false;
        }
        else
        {
          allselected = true;

          for(i=0; i<form.elements.length; i++)
          {
            if(form.elements[i].name.indexOf(fieldname+'[')>-1 && form.elements[i].checked==false)
              allselected = false;
          }
          
          if(allselected==true)
            form[fieldname+'_all'].checked = true;
          else
            form[fieldname+'_all'].checked = false;

        }
      }
    }
  }
  
  function myrex_clearform(form,array,text)
  {
    if(typeof(form)!='undefined' && typeof(array)!='undefined')
    {
      while(form.nodeName!="FORM" && form.nodeName!="BODY")
        form = form.parentNode;
      
      if(form.nodeName=="FORM" && array!='')
      {
        if(typeof(form[array+'[id]']) != 'undefined' && typeof(form[array+'[text]']) != 'undefined' && typeof(form[array+'[url]']) != 'undefined')
        {
          if(myrex_confirm(text,form))
          {
            form[array+'[id]'].value = form[array+'[text]'].value = form[array+'[url]'].value = '';
          }
        }
      }
    }
  }

  function myrex_unlock(form,suffix,onoff)
  {
    if(typeof(form)!='undefined' && typeof(suffix)!='undefined')
    {
      if(typeof(onoff) == "undefined")
        onoff = 0;
      
      if(onoff!=true)
        onoff = false;
      else
        onoff = true;
      
      for(i=0; i<form.elements.length; i++)
        if(form.elements[i].name.indexOf(suffix)>-1)
        {
          if(onoff)
          {
//            form.elements[i].style.background='#ffffff';
            form.elements[i].disabled=false;
          }  
          else
          {
//            form.elements[i].style.background='#cccccc';
            form.elements[i].disabled=true;
          }  
        }
    }
  }
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  function myrex_selectalloptions(selectbox,form,onoff)
  {
    if(typeof(onoff) == "undefined")
      onoff = 0;
    
    if(onoff!=1)
      onoff = false;
    else
      onoff = true;
      
    if(typeof(selectbox) == "object")
    {
      for(i=0; i<selectbox.length; i++)
        selectbox.options[i].selected=onoff;
    }
    else
    {
      while(form.nodeName!="FORM" && form.nodeName!="BODY")
        form = form.parentNode;
      
      if(form.nodeName=="FORM")
      {
        if(typeof(form[selectbox])!= "undefined")
          for(i=0; i<form[selectbox].length; i++)
            form[selectbox].options[i].selected=onoff;
      }
    }
  }

  function myrex_display(elementname,onoff)
  {
    if(typeof(onoff) == "undefined")
      onoff = 0;
    
    if(onoff!=true)
      onoff = false;
    else
      onoff = true;


    if(typeof(document.getElementById(elementname).style) != "undefined")
    {
      if(onoff)
        document.getElementById(elementname).style.display = 'block';
      else
        document.getElementById(elementname).style.display = 'none';
    }
  }
  function myrex_activate_repeatweeks(checkbox) {
    dayschecked = false;
    if(typeof(checkbox.name) != "undefined")
    {
      if(checkbox.name.indexOf('[repeat]')>0)
      {
        checkboxname = checkbox.name.substr(0,checkbox.name.indexOf('[repeat]'));

        if(typeof(checkbox.form[checkboxname+'[repeatselectbox]']) != "undefined")
        {
          for(i=0; i<document.getElementsByName(checkboxname+'[repeat][]').length; i++)
          {
            if(document.getElementsByName(checkboxname+'[repeat][]')[i].checked) dayschecked=true;
          }
      
          if(dayschecked)
          {
            checkbox.form[checkboxname+'[repeatselectbox]'].disabled=false;
            document.getElementById('udbox').style.display='block';
          }
          else
          {
            checkbox.form[checkboxname+'[repeatselectbox]'].disabled=true;
            document.getElementById('udbox').style.display='none';
          }
        }
      }
    }
  }
  
  function myrex_showextras(showelement,button,onoff)
  {

    if(typeof(document.getElementById(showelement)) != "undefined" && typeof(button) != "undefined")
    {
      if(typeof(onoff) == "undefined")
        if(document.getElementById(showelement).style.display=='block')
          onoff = false;
        else
          onoff = true;
      else
        if(onoff!=true)
          onoff = false;
        else
          onoff = true;

      if(onoff)
      {
        document.getElementById(showelement).style.display='block';
        button.innerHTML='-';
      }
      else
      {
        document.getElementById(showelement).style.display='none';
        button.innerHTML='+';
      }

    }
  }

  function myrex_settimebox(field,onoff)
  {
    if(typeof(onoff) != "undefined")
    {
      if(onoff!=true)
        onoff = false;
      else
        onoff = true;
        
      if(typeof(document.getElementById(field+'_hour')) != "undefined")
      {
        if(onoff)
          document.getElementById(field+'_hour').selectedIndex=0;
        document.getElementById(field+'_hour').disabled=onoff;
      }
      if(typeof(document.getElementById(field+'_minute')) != "undefined")
      {
        if(onoff)
          document.getElementById(field+'_minute').selectedIndex=0;
        document.getElementById(field+'_minute').disabled=onoff;
      }
    }
  }

  function myrex_deletedate (field)
  {
    if(typeof(document.getElementById(field)) != "undefined")
    {
      document.getElementById(field).value='';
      myrex_settimebox(field,true);
    }
  }
  
  function myrex_deselectStatus(form,name,onoff)
  { 
    if(typeof(name) != "undefined" && typeof(form) != "undefined")
    {
      if(typeof(onoff) == "undefined")
        onoff = false;
      else
        if(onoff!=true)
          onoff = false;
        else
          onoff = true;
          
      for(i=0; i<form.elements.length; i++)
      { 
        if(form.elements[i].name.indexOf(name+'[')>-1)
          form.elements[i].disabled = onoff;
      }
    }
  }
  
  
  
  var re_moveInterval;
  var re_ua = navigator.userAgent.toLowerCase();
  var re_layername = 're_tooltiplayer';

  function myrex_showTooltip(text) {
    myrex_hideTooltip(); // deletes an image which is already shown
  
    if(text!=undefined)
    {
      text = unescape(text);
       // creates a new DIV-Element...
      var newDIV = document.createElement("div");
          newDIV.setAttribute("id",re_layername); 
  
      if(document.all && re_ua.indexOf("msie")>-1) {
        // different syntax for setting the Style-attributes in MSIE
        newDIV.style.visibility = "hidden";
      }
            
      // creates the text to be shown in the DIV-Element
      // var newDIVText = document.createTextNode(text);
  
      // Sets the new elements onto the page
      document.getElementsByTagName("body")[0].appendChild(newDIV);
      // document.getElementById(re_layername).appendChild(newDIVText);
      document.getElementById(re_layername).innerHTML = text;
    
      // starts the function which lets the image follow the mouse-cursor
      re_moveInterval = window.setInterval("myrex_moveTooltip()", 10);
    }
  }
  
  function myrex_hideTooltip() {
    window.clearInterval(re_moveInterval); // stops the interval which lets the image follow the mouse-cursor
    
    if(document.getElementById(re_layername)!=undefined) {
      // deletes the image-div
      document.getElementById(re_layername).parentNode.removeChild(document.getElementById(re_layername));
    }
  }
  
  function myrex_moveTooltip() {
    // gets the image-size
    width = document.getElementById(re_layername).style.width; width = width.substring(0,width.length-2); width = parseInt(width);
    height= document.getElementById(re_layername).style.height; height = height.substring(0,height.length-2); height = parseInt(height);
    
    // gets the window-size
    docwidth=document.all? myrex_truebody().scrollLeft+myrex_truebody().clientWidth : pageXOffset+window.innerWidth-15
    docheight=document.all? Math.min(myrex_truebody().scrollHeight, myrex_truebody().clientHeight) : Math.min(document.body.offsetHeight, window.innerHeight)
  
    // if the image would be shown outside of the viewable area of the page, it will stuck to the borders
    if(docwidth < 18+xmouse-myrex_truebody().scrollLeft+width+12)
      xpos =myrex_truebody().scrollLeft+xmouse-width-16;
    else
      xpos = xmouse+10;
  
    if(docheight < 18+ymouse-myrex_truebody().scrollTop+height+12)
      ypos = myrex_truebody().scrollTop+ymouse-Math.max(0,(height+12 + ymouse - docheight));
    else
      ypos = ymouse+10;
  
    xpos = xpos+"px";
    ypos = ypos+"px";
    
    if(document.all && re_ua.indexOf("msie")>-1) {
      // different syntax for MSIE
  //    document.getElementById(re_layername).style.setAttribute("visibility","visible",false);
      document.getElementById(re_layername).style.setAttribute("top",ypos,false);
      document.getElementById(re_layername).style.setAttribute("left",xpos,false);
  //    document.getElementById(re_layername).style.setAttribute("width",width+"px",false);
  //    document.getElementById(re_layername).style.setAttribute("height",height+"px",false);
  //    document.getElementById(re_layername).style.setAttribute("position","absolute",false);
  //    document.getElementById(re_layername).style.setAttribute("border","solid 6px #ffffff",false);
  //    document.getElementById(re_layername).style.setAttribute("backgroundColor","#ffffff",false);
  //    document.getElementById(re_layername).style.setAttribute("backgroundImage","url(images/loading.gif)",false);
  //    document.getElementById(re_layername).style.setAttribute("backgroundRepeat","no-repeat",false);
  //    document.getElementById(re_layername).style.setAttribute("backgroundPosition","bottom right",false);
    } else {
  //    document.getElementById(re_layername).setAttribute("style","position: absolute; display: block; top: "+ypos+"; left: "+xpos+"; width: "+width+"px; height: "+height+"px; border: solid 6px #ffffff;background-color: #ffffff; background-image: url(images/loading.gif); background-repeat: no-repeat; background-position: bottom right;");
      document.getElementById(re_layername).setAttribute("style","display: block; top: "+ypos+"; left: "+xpos+";");
  
    }
  
    // show the image-DIV
    document.getElementById(re_layername).style.visibility = "visible";
  }
  
  function myrex_truebody()	{
    // right syntax for any browser (needed in "re_moveTooltip")
  	return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
  }
  
  
  /* Zum Speichern der Mausposition */
    var xmouse=0; var ymouse=0;  // Speichern die Mausposition
    navigator.appName ? 'Netscape' : document.captureEvents(Event.MOUSEMOVE);  // Mausposition erkennen fuer Netscape
    document.onmousemove = myrex_mauspos; // Die Mausposition wird bei jeder Mausbewegung neu geschrieben
  
  function myrex_mauspos(e) {
  /* Zum Abfragen der Mausposition - je nach Browser wird das anders erledigt. Am Ende werden die Daten fuer
     xmouse und ymouse in den globalen Variablen gespeichert. */
    if (navigator.appName == 'Netscape') {
       xmouse = e.pageX;
       ymouse = e.pageY;
    } else {
       xmouse = window.event.clientX;
       ymouse = window.event.clientY;
    }
    
    if(!(navigator.appName == 'Netscape')) {  
      if(document.documentElement && document.documentElement.scrollTop) {
        xmouse = xmouse + document.documentElement.scrollLeft;
        ymouse = ymouse + document.documentElement.scrollTop;
      } else if(document.body) {
        xmouse = xmouse + document.body.scrollLeft;
        ymouse = ymouse + document.body.scrollTop;
      }
    }
  }
