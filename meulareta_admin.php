<?php

if(isset($_POST['submitted'])) { //if something has been submitted
	
  if(!empty($_POST["doStatusUpdate"])) { //if the user selected to update their Twitter status
	$user = get_option("meulareta_user"); //get user
	$pass = get_option("meulareta_pass"); //get password
	$status = stripslashes($_POST["meulareta_status"]); //get status from posted variable
	
    if (!empty($status)){ //if status isn't empty
		if (!empty($user) && !empty($pass)) { // if user and password are set
			$result = myTwitterPoster($user,$pass,urlencode($status)); //post the status update to Twitter
			if ($result != "200") { // if the returned HTML code isn't 200 then there has been an error ?>
			    <div class="error"><p><strong>O Chío <i>non</i> foi publicado.  O usuario ou a contrasinal proporcionada é incorrecto.  Comproba a túa información de acceso abaixo e tentao de novo.</strong></p></div><?php
			}
			else {//the HTML code was 200 so give success message ?>
				<div class="updated"><p><strong>Publicado Chio en Lareta.
				Novo Chío: <i><?php echo $status; ?></i></strong></p></div><?php
			}
		}
		else { // user and password are not set
			?>
		    <div class="error"><p><strong>Debes proporcionar un usuario e contrasinal para actualizar os chíos en Lareta.</strong></p></div><?php
		}
	}
	else { //nothing to post because new status not entered ?>
		<div class="error"><p><strong>Insire algo no campo de texto para poñer un chío en Lareta.</strong></p></div> <?php
	}
  }
  elseif (!empty($_POST["default_restore"])) { //if the user wants to restore the default settings
	//reset all MyLateta Options
	//lareta account
	update_option("meulareta_user", "M3uLar3r3ta");
	update_option("meulareta_pass","");
	
	//display options
	update_option("meulareta_title", "MeuLareta");
	update_option("meulareta_count", 1);
	update_option("meulareta_cache_life",900);
	
	//formatting options
	update_option("meulareta_order", "putfirst_twitter");
	update_option("meulareta_separator", "&nbsp;--&nbsp;");
	update_option("meulareta_beforeall", '<ul class="meulareta">');
	update_option("meulareta_afterall", "</ul>");
	update_option("meulareta_beforeitem", '<li class="meulareta">');
	update_option("meulareta_afteritem", "</li>");
	
    ?>

    <div class="updated"><p><strong>Opci&oacute;ns restauradas á configuracio por defecto.</strong></p></div>

    <?php
  }
  else {//update options
    //twitter account options
	update_option("meulareta_user", $_POST['meulareta_user']);
	update_option("meulareta_pass", $_POST['meulareta_pass']);
	
	//display options
	update_option("meulareta_title", $_POST['meulareta_title']);
	update_option("meulareta_count", intval($_POST['meulareta_count']));
	update_option("meulareta_cache_life", intval($_POST['meulareta_cache_life']));
	
	//formatting options
	update_option("meulareta_order", $_POST['meulareta_order']);
	update_option("meulareta_separator", str_replace(" ", "&nbsp;", $_POST['meulareta_separator']));
	update_option("meulareta_beforeall", $_POST['meulareta_beforeall']);
	update_option("meulareta_afterall", $_POST['meulareta_afterall']);
	update_option("meulareta_beforeitem", $_POST['meulareta_beforeitem']);
	update_option("meulareta_afteritem", $_POST['meulareta_afteritem']);
    ?>
    <div class="updated"><p><strong>Options saved.</strong></p></div>
    <?php
  }
}
?>

<div class="wrap">
<h2>MeuLareta</h2>
<p>Emprega esta páxina de configuración para cambiar a configuración por defecto para amosar MeuLareta ou actualizar manualmente os teus Chíos recentes. Para amosar os Chíos recentes, insire a funcion meulareta() na barra lateral do tema ou onde desexes.</p>

<?php if(!function_exists("curl_init")) {?> 
<p><b>Actualización de Chíos desactivada:</b> A actualización do estado está bloqueada debiado á falta de soporte de <a href="http://curl.haxx.se/libcurl/php/">libcurl</a> do <a href="http://us2.php.net/curl">PHP</a>  do teu servidor.</p>
<?php } ?>

<?php if(!current_user_can('publish_posts')) { ?>
<p><b>Actualización de Chíos desactivada:</b>Non tes suficientes privilegios en Wordpress para enviar chíos.</p>
<?php } ?>

<p><b>Código de exemplo: </b><br />
&lt;?php if (function_exists('meulareta')) { ?&gt;<br />
&nbsp;&nbsp;&lt;li>&lt;? meulareta();?&gt;&lt;/li&gt;<br />
&lt;?php } ?></p>


<?php if(function_exists("curl_init") && current_user_can('publish_posts')) { ?>
<script type="text/javascript">
//<![CDATA[
var PressedButton;

function charCount() {
	var count = document.getElementById("meulareta_status").value.length;
	var status = document.getElementById("meulareta_status").value;
	if (count > 0) {
		if (count > 140) {
			document.meulareta_options.meulareta_status.value = status.substring(0,140);
			document.getElementById("meulareta_characters").innerHTML =  "No characters remaining";
		}
		else {
			document.getElementById("meulareta_characters").innerHTML = (140 - count) + " characters remaining";
		}
	}
	else {
		document.getElementById("meulareta_characters").innerHTML = "140 characters remaining.";
	}
}
setTimeout("charCount();", 500);
document.getElementById("meulareta_options").setAttribute("autocomplete", "off");

function isNumeric(fieldText)
{
   var ValidChars = "0123456789";
   var IsNumber = true;
   var Char;
 
   for (i = 0; i < fieldText.length && IsNumber == true; i++) { 
      Char = fieldText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) {IsNumber = false;}
   }
   return IsNumber;   
}

function doValidation(form) {
  if (PressedButton.name == 'doStatusUpdate') {
	return ValidateUpdate(form);
  }
  else {return ValidateForm(form);}
}

function ValidateForm(form)
{

   if (!isNumeric(form.meulareta_cache_life.value)) {	
      alert('Insire un número válido de segundos para o valor de vida da Caché.') 
      form.meulareta_cache_life.focus(); 
      return false; 
   }
   else if (Number(form.meulareta_cache_life.value) < 1) {
      alert('Insire un valor de vida da Caché (segundos) maior que 0.');
      form.meulareta_cache_life.focus(); 
      return false; 
   }
   else if (form.meulareta_user.value.length == 0) {
      alert('Insire un nome de usuario de Lareta ou deixao por defecto MeuLareta.');
      form.meulareta_user.focus(); 
      return false; 
   }
 
return true;
 
} 

function ValidateUpdate(form) {
	if (form.meulareta_user.value.length == 0) {
      alert('Insire un nome de usuario de Lareta ou deixao por defecto MeuLareta.');
      form.meulareta_user.focus(); 
      return false; 
   }
   else if(form.meulareta_pass.value.length == 0) {
      alert('Insire a túa contrasinal de Lareta para enviar chíos.');
      form.meulareta_pass.focus(); 
      return false; 
   }
   else if(form.meulareta_status.value.length == 0) {
	alert('Insire algo en "Que fas?" para enviar o chío.');
      form.meulareta_status.focus(); 
      return false;
   }
   return true;
}
//]]>
</script>
<?php } ?>

<form name="meulareta_options" action="<?php echo $_SERVER[PHP_SELF] ?>?page=meulareta/meulareta_admin.php" method="post" onsubmit="javascript:return doValidation(this)" autocomplete="off" >
<input type="hidden" name="submitted" />

<?php if(function_exists("curl_init") && current_user_can('publish_posts')) {?>
<fieldset name="meulareta_update" class="options">
<legend><?php _e('Update Twitter Status') ?></legend>
<table width="100%" cellspacing="0" cellpadding="0" class="optiontable editform">
<?php //STATUS ?>
<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_status">Que andas a facer?:</label>
		    </p>
		  </th>
		<td><p>
	      <textarea cols="40" maxlength="140" name="meulareta_status" id="meulareta_status" onkeyup="charCount();" ></textarea><br/>
		  

		
		  <span id="meulareta_characters">140 caracteres.</span><br />
		  <input type="submit" name="doStatusUpdate" value="Update Status" onmouseup="javascript:PressedButton=this;" /><br />
		  <b>Nota:</b> Para actualizar chíos en Lareta, insire no campo de texto e fai click en "Enviar Chío".
	      </p>
		</td>
	</tr>
	<tr>
		<th width="33%" valign="top" scope="row" style="vertical-align: middle;">
		Chió máis recente:
		</th>
		<td style="vertical-align: middle;"><p><?php meulareta_mostrecent(); ?></p></td>
	</tr>
</table>
</fieldset>
<?php } ?>

<?php //Login Settings ?>
<fieldset name="meulareta_basics" class="options">
<legend><?php _e('Twitter Login Settings') ?></legend>
<table width="100%" cellspacing="0" cellpadding="0" class="optiontable editform">
<?php //USER ?>
<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_user">Usuario en Lareta:</label>
		    </p>
		  </th>
		<td><p>
	      <input name="meulareta_user" type="text" value="<?php $template_display = get_option("meulareta_user");
			echo htmlspecialchars(stripslashes($template_display == NULL ? "twitter" : $template_display)); ?>" size="30" />		
	      <br/><b>Nota:</b> Se non insires nada, por defecto collerase "MeuLareta".  This is not case sensitive for login purposes, but it is case sensitive in order for the username to be removed from each tweet shown on your site (i.e. if it doesn't match "Username:" will be shown in front of each tweet).
	      </p></td>
	</tr>
	
	<?php //PASS 
	if(function_exists("curl_init") && current_user_can('publish_posts')) {?>
<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_pass">Twitter password:</label>
		    </p>
		  </th>
		<td><p>
	      <input type="password" size="25" name="meulareta_pass" id="meulareta_pass" value="<?php echo get_option("meulareta_pass"); ?>" />		
	      <br/><b>Note:</b> This is currently only used when you post updates to Twitter from the Options->MeuLareta page in Wordpress.  It is not necessary to enter your password if you only want to display your recent Twitter status updates.
	      </p></td>
	</tr>
	<?php } ?>
	</table>
</fieldset>



<?php //Display Settings ?>
<fieldset name="meulareta_display" class="options">
<legend><?php _e('Display Settings') ?></legend>
<table width="100%" cellspacing="0" cellpadding="0" class="optiontable editform">	
	<?php //TITLE ?>
	<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_title">Title:</label>
		    </p>
		  </th>
		<td><p>
	      <input name="meulareta_title" type="text" value="<?php $template_display = get_option("meulareta_title");
			echo htmlspecialchars(stripslashes($template_display == NULL ? "MeuLareta" : $template_display)); ?>" size="30" />		
	      <br/><b>Note:</b> O título será amosado antes dos chíos recentes.
	      </p></td>
	</tr>
	
	<?php //COUNT ?>
	<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_count">Número de Chíos:</label>
		    </p>
		  </th>
		<td><p>
<select name="meulareta_count">
<?php 
$option_count = get_option("meulareta_count");
$i=1;
while($i<=20) { ?>
  <option value="<?php echo $i;?>" <?php if($i == $option_count){echo "selected=\"selected\"";}?>><?php echo $i;?></option>
  <?php $i++;} ?>
</select>
	      <br/><b>Nota:</b> Selecciona o n&uacute;mero de ch&iacute;os recentes a mostrar.  O m&aacute;ximo &eacute; 20; por defecto &eacute; 1.  Se o número de chíos mostrados é menor que o número, quere decir que o só estan dispoñibles eses neste momento.
	      </p></td>
	</tr>
	<tr>
		<th width="33%" valign="top" scope="row"><p><label for="meulareta_cache_life">Cache Life:</label></p>
		</th>
		<td><input name="meulareta_cache_life" type="text" value="<?php $template_display = get_option("meulareta_cache_life");
			echo htmlspecialchars(stripslashes($template_display == NULL ? "900" : $template_display)); ?>" size="4" maxlength="4" /><br />
			<b>Note:</b> A vida da caché determina canto tempo se mantén gardada a fonte no servidor antes de actualizala. O número esta en segundos.  Por exemplo, 300 segundos = 5 minutos.  A configuración recomendada é 5 minutos ou superior par un uso normal para evitar sobrecargar Lareta con peticións innecesarias.  A configuración por defecto é 900 segundos (15 minutos).
		</td>
	</tr>
	
</table>
</fieldset>

<?php //Formatting Options ?>
<fieldset name="meulareta_basics" class="options">
<legend><?php _e('Formatting Options') ?></legend>
<table width="100%" cellspacing="0" cellpadding="0" class="optiontable editform">
	<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_order">Orde dos Elementos (Ch&iacute;o &amp;amp; Data):</label>
			</p>
		</th>
		<td><p>
			<select name="meulareta_order">
				<option value="putfirst_twitter"<?php if(get_option("meulareta_order") == "putfirst_twitter") {echo ' selected="selected"';}?>>Ch&iacute;o - Data</option>
				<option value="putfirst_time"<?php if(get_option("meulareta_order") == "putfirst_time") {echo ' selected="selected"';}?>>Data - Ch&iacute;o</option>
			</select><br />
			<b>Nota:</b> Se &eacute; o ch&iacute;o primeiro, mostrar&aacute; os ch&iacute;os seguidos dun separador e a data (p.e. &quot;Estou facendo isto - fai 5 minutos&quot;). Se &eacute; a data primeiro amosar&aacute; a data primeiro seguido do separador escollido e logo o ch&iacute;o (p.e. "fai 5 minutos - Estou facendo isto").
		</p></td>
	</tr>
	
	<tr>
		<th width="33%" valign="top" scope="row">
			<p><label for="meulareta_separator">Separador:</label></p>
		</th>
		<td><p>
			<input name="meulareta_separator" type="text" value="<?php $template_display = get_option("meulareta_separator"); $template_display = str_replace("&nbsp;"," ",$template_display);
			echo $template_display == NULL ? "" : $template_display; ?>" size="30" maxlength="30" /><br />
			<b>Note:</b> Insire o texto/HTML que queiras que apareza entre o Chío e a Data.  Asegurate de
			que inclues un espazo ó inicio e final a menos que quieras todo xunto.  Por defecto é " -- ".
		</p></td>
	</tr>

	<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_beforeall">Antes de tódolos chíos:</label>
			</p>
		  </th>
		<td><p>
		  <input name="meulareta_beforeall" type="text" value="<?php $template_display = get_option("meulareta_beforeall");
			echo htmlspecialchars(stripslashes($template_display == NULL ? "<ul>" : $template_display)); ?>" size="30" />		
		  <br/><b>Note:</b> Insire o HTML formateado que queres que ter antes de cada ch&iacute;o recente.  Por defecto é un elemento de lista (&lt;li&gt;).
		  </p></td>
	</tr>
	
		<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_afterall">Despois de tódolos chíos:</label>
			</p>
		  </th>
		<td><p>
		  <input name="meulareta_afterall" type="text" value="<?php $template_display = get_option("meulareta_afterall");
			echo htmlspecialchars(stripslashes($template_display == NULL ? "</ul>" : $template_display)); ?>" size="30" />		
		  <br/><b>Nota:</b> Insire o HTML formateado que queres que ter despois de cada ch&iacute;o recente.  Por defecto é un elemento de lista (&lt;/li&gt;).
		  </p></td>
	</tr>
	
	<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_beforeitem">Antes de tódolos Lareta</label>
			</p>
		  </th>
		<td><p>
		  <input name="meulareta_beforeitem" type="text" value="<?php $template_display = get_option("meulareta_beforeitem");
			echo htmlspecialchars(stripslashes($template_display == NULL ? "<li>" : $template_display)); ?>" size="30" />		
		  <br/><b>Nota:</b> Insire o HTML formateado que queres que ter antes de cada ch&iacute;o.  Por defecto é un elemento de lista (&lt;li&gt;).
		  </p></td>
	</tr>

	<tr>
		<th width="33%" valign="top" scope="row"><p>
		  <label for="meulareta_afteritem">Despois de tódolos Lareta</label>
			</p>
		  </th>
		<td><p>
		  <input name="meulareta_afteritem" type="text" value="<?php $template_display = get_option("meulareta_afteritem");
			echo htmlspecialchars(stripslashes($template_display == NULL ? "</li>" : $template_display)); ?>" size="30" />		
		  <br/><b>Nota:</b> Insire o HTML formateado que queres que ter despois de cada ch&iacute;o.  Por defecto é un elemento de lista (&lt;/li&gt;).
		  </p></td>
	</tr>
		

</table>
</fieldset>

<table width="100%" cellspacing="2" cellpadding="5" >
<tr>
		<td style="text-align: right;" colspan="2">
		    <p class="submit">
		    	<input type="submit" name="submit" value="Actualizar Opci&oacute;ns" onmouseup="javascript:PressedButton=this;" />
				<input type="submit" name="default_restore" value="Restaurar Configuraci&oacute;ns por Defecto" />
		  	</p>
</td>
</tr>
</table>


</div>
