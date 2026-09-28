<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Employee';
$base = '..';
$errors = [];
if (is_post()) {
 try {
  $name=posted('full_name');$phone=posted('phone');$email=posted('email');$position=posted('position');$branch=posted('branch_id');$hire=posted('hire_date');$status=posted('employment_status');
  if($name==='')$errors[]='Full name is required.'; if($phone==='')$errors[]='Phone is required.'; if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Valid email is required.';
  if($position==='')$errors[]='Position is required.'; if(!ctype_digit($branch))$errors[]='Branch is required.'; if($hire==='')$errors[]='Hire date is required.';
  if(!in_array($status,enum_values('employment_status'),true))$errors[]='Invalid employment status.';
  if(!$errors){db_insert('INSERT INTO employees(full_name,phone,email,position,branch_id,hire_date,employment_status) VALUES(:n,:p,:e,:pos,:b,:h,:s)',
  [':n'=>$name,':p'=>$phone,':e'=>$email,':pos'=>$position,':b'=>(int)$branch,':h'=>$hire,':s'=>$status]);flash_set('success','Employee added successfully.');redirect('employee.php');}
 }catch(Throwable $e){$errors[]=exception_message($e);}
}
$branches=db_select_all('SELECT branch_id,branch_name FROM branches ORDER BY branch_name');
include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label for="full_name">Full Name *</label><input type="text" name="full_name" id="full_name" value="<?= e(posted('full_name')) ?>" maxlength="100" required></div><div class="field"><label for="phone">Phone *</label><input type="text" name="phone" id="phone" value="<?= e(posted('phone')) ?>" maxlength="20" required></div><div class="field"><label for="email">Email *</label><input type="email" name="email" id="email" value="<?= e(posted('email')) ?>" maxlength="100" required></div><div class="field"><label for="position">Position *</label><input type="text" name="position" id="position" value="<?= e(posted('position')) ?>" maxlength="100" required></div><div class="field"><label for="branch_id">Branch *</label><?php
$opts = db_select_all('SELECT branch_id,branch_name FROM branches ORDER BY branch_name');
$current = posted('branch_id');
?><select name="branch_id" id="branch_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['branch_id']) ?>" <?= (string)$current === (string)$opt['branch_id'] ? 'selected' : '' ?>><?= e($opt['branch_name']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label for="hire_date">Hire Date *</label><input type="date" name="hire_date" id="hire_date" value="<?= e(posted('hire_date')) ?>" required></div><div class="field"><label for="employment_status">Employment Status *</label><?php $current = posted('employment_status'); ?><select name="employment_status" id="employment_status" required><option value="">-- Select --</option><?php foreach (enum_values('employment_status') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>