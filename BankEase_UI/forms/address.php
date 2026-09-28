<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Address';
$base = '..';
$errors = [];
if(is_post()){
 try{$customer=posted('customer_id');$type=posted('address_type');$line=posted('address_line');$city=posted('city');$district=posted('district');
 if(!ctype_digit($customer))$errors[]='Customer is required.';if(!in_array($type,enum_values('address_type'),true))$errors[]='Invalid address type.';if($line==='')$errors[]='Address line is required.';if($city==='')$errors[]='City is required.';if($district==='')$errors[]='District is required.';
 if(!$errors){db_insert('INSERT INTO addresses(customer_id,address_type,address_line,post_office,thana,city,district,postal_code,country,is_primary) VALUES(:c,:t,:l,:po,:th,:city,:d,:pc,:country,:p)',[
 ':c'=>(int)$customer,':t'=>$type,':l'=>$line,':po'=>posted_or_null('post_office'),':th'=>posted_or_null('thana'),':city'=>$city,':d'=>$district,':pc'=>posted_or_null('postal_code'),':country'=>posted('country')?:'Bangladesh',':p'=>isset($_POST['is_primary'])?1:0]);flash_set('success','Address added successfully.');redirect('address.php');}}
 catch(Throwable $e){$errors[]=exception_message($e);}}
$customers=db_select_all('SELECT customer_id,cif_number,full_name FROM customers ORDER BY full_name');
include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label>Customer *</label><?php
$opts = db_select_all("SELECT customer_id, CONCAT(cif_number,' - ',full_name) AS label FROM customers ORDER BY full_name");
$current = posted('customer_id');
?><select name="customer_id" id="customer_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['customer_id']) ?>" <?= (string)$current === (string)$opt['customer_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label>Address Type *</label><?php $current = posted('address_type'); ?><select name="address_type" id="address_type" required><option value="">-- Select --</option><?php foreach (enum_values('address_type') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div><div class="field full"><label for="address_line">Address Line *</label><input type="text" name="address_line" id="address_line" value="<?= e(posted('address_line')) ?>" maxlength="255" required></div><div class="field"><label for="post_office">Post Office</label><input type="text" name="post_office" id="post_office" value="<?= e(posted('post_office')) ?>" maxlength="100"></div><div class="field"><label for="thana">Thana</label><input type="text" name="thana" id="thana" value="<?= e(posted('thana')) ?>" maxlength="100"></div><div class="field"><label for="city">City *</label><input type="text" name="city" id="city" value="<?= e(posted('city')) ?>" maxlength="100" required></div><div class="field"><label for="district">District *</label><input type="text" name="district" id="district" value="<?= e(posted('district')) ?>" maxlength="100" required></div><div class="field"><label for="postal_code">Postal Code</label><input type="text" name="postal_code" id="postal_code" value="<?= e(posted('postal_code')) ?>" maxlength="20"></div><div class="field"><label for="country">Country *</label><input type="text" name="country" id="country" value="<?= e(posted('country')) ?>" maxlength="100" required></div><div class="field"><label>Primary Address</label><input type="checkbox" name="is_primary" value="1"></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>