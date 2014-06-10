<?php
?>

<script language="JavaScript" type="text/javascript">
$( document ).ready(function() {
		
		function showEditor(e){
				var tr = $(e).closest("tr");
				tr.removeClass("stealth");
				tr.find(".editorbuttons").show();
				tr.find(".stdbuttons").hide();
		}
		
		function cancelEditor(e){
				var tr = $(e).closest("tr");
				tr.addClass("stealth");
				tr.find(".editorbuttons").hide();
				tr.find(".stdbuttons").show();
				tr.find("input").each(function(){
						var e = $(this);
						if(e.attr("type")=="text"){
								e.val(e.attr("oldvalue"));
						}else if(e.attr("type")=="checkbox"){
								if(e.attr("oldvalue")==1){
										e.attr("value",1);
										e.attr("checked","checked");
										e.trigger("change");
								}else{
										e.attr("value",0);
										e.removeAttr("checked");
										e.trigger("change");
								}
						}else if(e.attr("type")=="color"){
								e.val(e.attr("oldvalue"));
								e.trigger("change");
						}
				});
		}
		
		function initRow(data){
				data.find('.button-change').button({ text: false,	icons: { primary: 'ui-icon-check' }	}).on("click",function(e){
						e.preventDefault();
						var form = $(this).closest("form");
						var fdata = serializeForm(form);
						var tr = $(this).closest(".mainrow");
						$.ajax({
								url:"gruppenVerwalten.php",
								data:fdata+"&zutun=2",
								success:function(data,state,xhr){
										if(data.length > 0){
												tr.replaceWith(initRow($(data)));
										}
								}
						});
				});
				
				data.find('.button-cancel').button({ text: false,	icons: { primary: 'ui-icon-close'	}	}).on("click",function(e){
						e.preventDefault();
						cancelEditor($(this));
				});
				
				data.find('.button-delete').button({ text: false,	icons: { primary: 'ui-icon-minus'	}	}).on("click",function(e){
						e.preventDefault();
						var form = $(this).closest("form");
						var fdata = "gid="+form.find("input[name='gid']").val();
						var tr = $(this).closest(".mainrow");
						$.ajax({
								url:"gruppenVerwalten.php",
								data:fdata+"&zutun=3",
								success:function(data,state,xhr){
										tr.remove();
								}
						});
				});
				
				data.find('input[type="color"]').spectrum();

				data.find('input[type="checkbox"]').each(function(){
						var that = $(this);
						
						that.switchButton({
								on_label: 'Snipe einen Artikel',
								off_label: 'Snipe alle Artikel',
								labels_placement: "right",
								checked: ((typeof that.attr("checked") !== 'undefined' && that.attr("checked") !== false)?true:false)
						});
				});
				
				data.find('.stealth input[type="text"]').on("focus",function(e){
						showEditor(this);
				});
				
				data.find('.stealth .switch-wrapper, .stealth .switch-button-background, .stealth .switch-button-label, .stealth .switch-button-button').on("click",function(e){
						showEditor(this);
				});
				
				data.find('.stealth .sp-replacer').on("click",function(){
						showEditor(this);
				});
				
				return data;
		}
		
		function serializeForm(form){
				var data = form.serialize();
				form.find("input[type='checkbox']").each(function(e){
						data += "&"+$(this).attr("name")+"=";
						data += (typeof $(this).attr("checked") !== 'undefined' && $(this).attr("checked") !== false)?1:0;
				});
				return data;
		}
		
		$('.adder .button-add').button({ text: false, icons: { primary: 'ui-icon-plus' } }).on("click",function(e){
				e.preventDefault();
				var form = $(this).closest("form");
				var fdata = serializeForm(form);
				form.find("input[type='text']").val("");
				form.find("input[name='color']").val("#0063d1");
				form.find("input[type='checkbox']").val("0").removeAttr("checked").trigger("change");
				$.ajax({
						url:"gruppenVerwalten.php",
						data:fdata+"&zutun=1",
						success:function(data,state,xhr){
								if(data.length > 0){
										$('#groupadder').after(initRow($(data)));
								}
						}
				});
		});
		
		$('.Inhaltstabelle').each(function(){
				initRow($(this));		
		});
});
</script>
<table style="border:none; width:1200px" class="grouplist">
		<tr id="groupadder">
				<td>
						<form class="adder">
								<table class="Inhaltstabelle">
										<tr class="liner">
												<td style="width:51px;">
														<input type="color" name="farbe" value="#0063d1" />
												</td>
												<td style="width:156px;"><input type="text" name="name" value="" placeholder="Name ..." style="width:150px;" /></td>
												<td><input type="text" name="notizen" value="" placeholder="Notizen ..." style="width:100%;" /></td>
												<td style="width:160px;"><div class="switch-wrapper"><input type="checkbox" value="0" name="nureins" /></div></td>
												<td style="width:60px; text-align:right;">
														<button class="button-add ui-color-green" title="Speichern">Speichern</button>
												</td>
										</tr>
								</table>
						</form>
				</td>
		</tr>
		<?php
		$sql = "SELECT * FROM gruppen";
		$gruppen = $db->get_results($sql);
        if($gruppen) {
            foreach($gruppen as $gruppe){
            ?>
            <tr class="mainrow">
                    <td>
                            <form class="changer">
                                    <table class="Inhaltstabelle">
                                            <tr class="liner stealth">
                                                    <td style="width:51px;">
                                                            <input type="color" name="farbe" value="<?=$gruppe->farbe?>" oldvalue="<?=$gruppe->farbe?>" />
                                                    </td>
                                                    <td style="width:156px;"><input type="text" name="name" value="<?=$gruppe->name?>" oldvalue="<?=$gruppe->name?>" placeholder="Name ..." style="width:150px;" /></td>
                                                    <td><input type="text" name="notizen" value="<?=$gruppe->notizen?>" oldvalue="<?=$gruppe->notizen?>" placeholder="Notizen ..." style="width:100%;" /></td>
                                                    <td style="width:160px;"><div class="switch-wrapper"><input name="nureins" type="checkbox" value="<?=$gruppe->nureins?>" oldvalue="<?=$gruppe->nureins?>" <?=($gruppe->nureins == 1)?"checked='checked'":""?>/></div></td>
                                                    <td style="width:60px; text-align:right;">
                                                                    <input type="hidden" value="<?=$gruppe->gruppeID?>" name="gid" />
                                                                    <button style="display: none" class="button-change ui-color-green editorbuttons" title="Ändern">Ändern</button>
                                                                    <button style="display: none" class="button-cancel ui-color-red editorbuttons" title="Abbrechen">Abbrechen</button>
                                                                    <button class="button-delete ui-color-red stdbuttons" title="Löschen">Löschen</button>
                                                    </td>
                                            </tr>
                                    </table>
                            </form>
                    </td>
            </tr>
            <?php }
        }?>
</table>
