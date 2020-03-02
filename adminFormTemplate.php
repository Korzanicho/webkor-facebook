<div class="wrap">
		<div id="icon-link" class="icon32"></div><h2><?="Ustawienia Webkor Facebook Box'a" ?></h2>
		<?php $this->displayAdminNotices( true ); ?>
		<div id="poststuff" class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">
					<form method="post" action="<?= WKFBOX_ADMIN_URL; ?>">
						<input type="hidden" name="wkfbSaveAction" value="submit" />
						<div class="postbox">
							<h3><span><?="Ustawienia" ?></span></h3>
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row"><?="Aktywny?" ?></th>
											<td>
												<select name="wkfb_status">
													<option value="enabled"<?= 'enabled' == $this->cf['status'] ? ' selected="selected"' : null; ?>>Aktywny</option>
													<option value="disabled"<?= 'disabled' == $this->cf['status'] ? ' selected="selected"' : null; ?>>Niekatywny</option>
												</select>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?= "Adres strony na Facebooku" ?></strong></th>
											<td>http://www.facebook.com/ <input type="text" name="wkfb_url" value="<?php echo $this->cf['url']; ?>" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?= "Wysokość boksu" ?></th>
											<td><input type="text" name="wkfb_height" value="<?= $this->cf['height']; ?>" size="3"/>&nbsp;px</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?="Szerokość boksu"; ?></th>
											<td><input type="text" name="wkfb_width" value="<?= $this->cf['width']; ?>" size="3"/> px</td>
										</tr>
										<tr valign="top">
												<th scope="row"><?="Automatyczne dostosowanie szerokości"; ?></th>
												<td>
													<input 
														value="on" 
														type="checkbox" 
														name="wkfb_adaptive_width"
														<?php echo $this->cf['adaptative'] == "true" ? 'checked' : '';?>
													/>
												</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?= "Pokaż zdjęcia profilowe znajomych" ?></th>
											<td>
												<input 
													value="on" 
													type="checkbox" 
													name="wkfb_faces" 
													<?php echo $this->cf['friendsFaces'] == "true" ? 'checked' : '';?>
												/>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?= "Pokaż posty"; ?></th>
											<td>
												<input 
													type="checkbox" 
													value="on" 
													name="wkfb_stream" 
													<?php echo $this->cf['showPosts'] == "true" ? 'checked' : '';?>
												/>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">
													<?= "Ukryj CTA" ?><br>
													<span style="font-size: 10px;"><?= "Ukryj przycisk CTA (jeżeli jest dostępny)"; ?></span>
											</th>
											<td>
												<input 
													value="on" 
													type="checkbox" 
													name="wkfb_cta"
													<?php echo $this->cf['hideCta'] == "true" ? 'checked' : '';?>
												>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?="Ukryj zdjęcie w tle"; ?><br /><span style="font-size: 10px"><?="Ukryj zdjęcie w tyle w nagłówku"; ?></span></th>
											<td>
												<input 
													type="checkbox" 
													value="on" 
													name="wkfb_header"
													<?php echo $this->cf['hideCover'] == "true" ? 'checked' : '';?>
												/>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?="Mały nagłówek"; ?><br /><span style="font-size: 10px"><?="Użyj mniejszego nagłówka"; ?></span></th>
											<td>
												<input 
													type="checkbox" 
													value="on" 
													name="wkfb_small_header"
													<?php echo $this->cf['smallHeader'] == "true" ? 'checked' : '';?>
												/>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?= "Język" ?><br /><span style="font-size: 10px"><?= "Zmiana może nie być widoczna automatycznie" ?></span></th>
											<td><?php echo $locales_input; ?></td>
										</tr>
										<tr valign="top">
											<th scope="row">
													<?= "Zakładki" ?>
											</th>
											<td>
												<label>
													<input type="checkbox" value="on" name="wkfb_timeline" <?php echo $this->cf['timeLine'] == "timeline," ? 'checked' : '';?>/> Oś czasu <br>
												</label>
												<label>
													<input type="checkbox" value="on" name="wkfb_messages" <?php echo $this->cf['messages'] == "messages," ? 'checked' : '';?>/> Wiadomości
												</label>
												<label>
													<input type="checkbox" value="on" name="wkfb_events" <?php echo $this->cf['events'] == "events" ? 'checked' : '';?>/> Wydarzenia <br>
												</label>
											</td>
										</tr>
										<?= apply_filters('aspexifblikebox_admin_settings', ''); ?>
									</tbody>
								</table>
							</div>
						</div>

						<p>
							<input class="button-primary" type="submit" name="send" value="<?= "Zapisz wszystkie ustawienia" ?>" id="submitbutton" />
							<input class="button-secondary" type="submit" name="preview" value="<?= "Zapisz i zobacz podgląd"; ?>" id="previewbutton" />
						</p>

						<div class="postbox">
							<h3><span><?= "Ustawienia ikony"; ?></span></h3>
							<div class="inside">
								<table class="form-table">
									<tbody>
									<tr valign="top">
										<th scope="row">
											Odległość od krawędzi<br />
											<span style="font-size: 10px">Określ pustą przestrzeń między ikoną, a krawędzią strony</span>
										</th>
										<td>
											<input type="text" name="wkfb_btspace" value="<?= $this->cf['edgeSpace'] ?>" size="3"/> px</td>
									</tr>
									<tr valign="top">
										<th scope="row">Położenie ikony</th>
										<td><input type="radio" name="wkfb_btvertical" value="top" <?= $this->cf['iconVertical'] == "top" ? 'checked' : '';?>/>Na górze box'a<br />
											<input type="radio" name="wkfb_btvertical" value="middle" <?= $this->cf['iconVertical'] == "middle" ? 'checked' : '';?>/>Na środku box'a<br />
											<input type="radio" name="wkfb_btvertical" value="bottom" <?= $this->cf['iconVertical'] == "bottom" ? 'checked' : '';?>/>Na dole box'a<br />
											<input type="radio" name="wkfb_btvertical" value="fixed" <?= $this->cf['iconVertical'] == "fixed" ? 'checked' : '';?>/>Stała wartość
											<input type="text" name="wkfb_btvertical_val" value="<?= $this->cf['iconVerticalConst'];?>" size="3" /> px (od góry box'a)
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">Obrazek ikony</th>
										<td>
											<span>
												<input type="radio" name="wkfb_btimage" value="fb1-right.png" <?php echo $this->cf['fbIcon'] == "fb1-right.png" ? 'checked' : '';?>/>
												<img src="<?php echo WKFBOX_URL.'images/fb1-right.png'; ?>" alt="Facebook" style="cursor:pointer;" />
											</span>
											<span>
												<input type="radio" name="wkfb_btimage" value="fb2-top.png" <?php echo $this->cf['fbIcon'] == "fb2-top.png" ? 'checked' : '';?> />
												<img src="<?php echo WKFBOX_URL.'images/fb2-top.png'; ?>" alt="Facebook" style="cursor:pointer;" />
											</span>
										</td>
									</tr>
									</tbody>
								</table>
							</div>
						</div>

						<p>
							<input class="button-primary" type="submit" name="send" value="Zapisz wszystkie zmiany" id="submitbutton" />
							<input class="button-secondary" type="submit" name="preview" value="Zapisz i zobacz podgląd" id="previewbutton" />
						</p>

						<div class="postbox">
							<h3><span>Zaawansowane ustawienia wyglądu</span></h3>
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row">Animuj przy załadowaniu strony</th>
											<td>
												<input type="checkbox" value="on" name="wkfb_animate_on_page_load" disabled readonly />        
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">Położenie</th>
											<td>
												<select name="wkfb_placement" disabled readonly>
													<option value="left">Po lewej</option>
													<option value="right" selected="selected">Po prawej</option>
												</select></td>
										</tr>
										<tr valign="top">
											<th scope="row">Położenie w pionie</th>
											<td>
												<input type="radio" name="wkfb_vertical" value="middle" checked disabled readonly />Na środku<br />
												<input type="radio" name="wkfb_vertical" value="fixed" disabled readonly /> Stała wartość
												<input type="text" name="wkfb_vertical_val" value="" size="3" disabled readonly />px Od góry strony
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">Kolor Obramowania</th>
											<td><input type="text" name="wkfb_bordercolor" class="bordercolor-field" value="#3B5998" size="6" disabled readonly /></td>
										</tr>
										<tr valign="top">
												<th scope="row">Szerokość obramowania</th>
												<td><input type="text" name="wkfb_borderwidth" value="2" size="3" disabled readonly />px</td>
										</tr>
										<tr valign="top">
											<th scope="row">Wysuń</th>
											<td>
												<select name="wkfb_slideon" disabled readonly>
													<option value="hover" selected="selected">Po najechaniu</option>
													<option value="click">Po kliknięciu</option>
												</select>
											</td>
										</tr>
										<tr valign="top">
												<th scope="row">Prędkość wysuwania</th>
												<td><input type="text" name="wkfb_slidetime" value="400" size="3" disabled readonly />milisekund</td>
										</tr>
										<tr valign="top">
											<th scope="row">Autootwieranie</th>
											<td>
												<input type="checkbox" value="on" name="wkfb_autoopen" disabled readonly /><br>
												Automatycznie otwórz po <input type="text" name="wkfb_autoopentime" value="400" size="3" disabled readonly /> milisekundach (1000 millisekund = 1 sekunda)
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">Auto zamykanie</th>
											<td>
												<input type="checkbox" value="on" name="wkfb_autoopen" disabled readonly /><br>
												Automatycznie zamknij po <input type="text" name="wkfb_autoopentime" value="400" size="3" disabled readonly />milisekunach (1000 millisekund = 1 sekunda)
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">Automatyczne otwieranie po zjechaniu na dół strony</th>
											<td><input type="checkbox" value="on" name="wkfb_autoopenonbottom" disabled readonly /></td>
										</tr>
										<tr valign="top">
											<th scope="row">Otwórz automatycznie gdy użytkownik przejdzie do określonej pozycji</th>
											<td>
												<input type="checkbox" value="on" name="wkfb_autoopenonposition" disabled readonly /><br>
												Otwórz automatycznie gdy użytkownik jest: <input type="text" disabled readonly name="wkfb_autoopenonposition_px" size="5">px od:
												<select name="wkfb_autoopenonposition_name" disabled readonly>
													<option value="top">Góry</option>
													<option value="bottom">Dołu</option>
												</select><br>
											</td>
										</tr>
										<tr valign="top">
												<th scope="row">Otwórz automatycznie gdy użytkownik zobaczy element</th>
												<td>
													<input type="checkbox" value="on" name="wkfb_autoopenonelement" disabled readonly /><br>
													Otwórz gdy użytkownik zobaczy: <input type="text" disabled readonly name="wkfb_autoopenonelement_name" size="10" value=""><small> selektor jQuery na przykład #element_id, .some_class)</small><br>
												</td>
										</tr>
										<tr valign="top">
											<th scope="row">Opóźnij ładowanie boxu<br /><span style="font-size: 10px">Zaznaczenie tego pola uniemożliwi ładowanie treści na Facebooku podczas ładowania całej strony. Gdy to pole jest zaznaczone, strona ładuje się szybciej, ale treść na Facebooku może pojawić się nieco później podczas otwierania okna po raz pierwszy.</span></th>
											<td><input type="checkbox" value="on" name="afbsb_async" disabled readonly /></td>
										</tr>
										<tr valign="top">
											<th scope="row">Wyłącz dla zmiennej GET<br /><span style="font-size: 10px">Przykładowo, ustawiając Parametr=iframe i Wartosc=true, Like Box nie wyświetli się na stronach zawierających te zmienne w adresie, np. yourwebsite.com/?iframe=true.</span></th>
											<td>
												Parametr: <input type="text" name="wkfb_disableparam" value="" size="6" disabled readonly /><br />
												Wartość: <input type="text" name="wkfb_disableval" value="" size="6" disabled readonly />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">Wyłącz dla postów/stron (oddzielone przecinkami)</th>
											<td>
												<input type="text" name="wkfb_disabled_on_ids" value="" disabled readonly />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">Wyłącz dla postów</th>
											<td>
												<input type="checkbox" value="on" name="wkfb_disabled_on_posts" disabled readonly />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">Wyłącz na stronach</th>
											<td>
												<input type="checkbox" value="on" name="wkfb_disabled_on_pages" disabled readonly />
											</td>
										</tr>
										<?php
											$types = get_post_types();
											unset($types['post']);
											unset($types['page']);
											unset($types['attachment']);
											unset($types['revision']);
											unset($types['nav_menu_item']);
											unset($types['custom_css']);
											unset($types['customize_changeset']);
											unset($types['oembed_cache']);
											if( count( $types ) > 0 ) {
										?>
											<tr valign="top">
												<th scope="row"><?php _e('Disable on post types:', 'aspexifblikebox'); ?></th>
												<td>
													<?php
													foreach ($types as $post_type) {
														echo '<input type="checkbox" value="' . $post_type . '" name="wkfb_disabled_on_posttypes[]" disabled readonly /> ' . $post_type . '<br>';
													}
													?>
												</td>
											</tr>
										<?php } ?>
										<tr valign="top">
											<th scope="row">Wyłącz na małych ekranach<br /><span style="font-size: 10px">Automatycznie ukryj baner jeśli jego szerokość jest większa niż szerokość urządzenia</span></th>
											<td><input type="checkbox" value="on" name="wkfb_smallscreens" checked disabled readonly /></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<p>
							<input class="button-primary" type="submit" name="send" value="Zapisz wszystkie zmiany" id="submitbutton" />
							<input class="button-secondary" type="submit" name="preview" value="Zapisz i zobacz podgląd" id="previewbutton" />
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>