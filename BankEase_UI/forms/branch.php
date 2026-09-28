<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Branch';
$base = '..';
$errors = [];
if (is_post()) {
  try {
    $name=posted('branch_name'); $address=posted('address'); $phone=posted('phone'); $manager=posted_or_null('manager_employee_id');
    $opening=posted('opening_date'); $status=posted('status');
    if ($name==='') $errors[]='Branch name is required.';
    if ($address==='') $errors[]='Address is required.';
    if ($phone==='') $errors[]='Phone is required.';
    if ($opening==='') $errors[]='Opening date is required.';
    if (!in_array($status,enum_values('branch_status'),true)) $errors[]='Invalid branch status.';
    if (!$errors) {
      db_insert('INSERT INTO branches (branch_name,address,phone,manager_employee_id,opening_date,status) VALUES (:n,:a,:p,:m,:d,:s)',
        [':n'=>$name,':a'=>$address,':p'=>$phone,':m'=>$manager,':d'=>$opening,':s'=>$status]);
      flash_set('success','Branch added successfully.'); redirect('branch.php');
    }
  } catch(Throwable $e){$errors[] = exception_message($e);}
}
$managers=db_select_all('SELECT employee_id,full_name FROM employees ORDER BY full_name');
include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label for="branch_name">Branch Name *</label><input type="text" name="branch_name" id="branch_name" value="<?= e(posted('branch_name')) ?>" maxlength="100" required></div><div class="field"><label for="address">Address *</label><input type="text" name="address" id="address" value="<?= e(posted('address')) ?>" maxlength="255" required></div><div class="field"><label for="phone">Phone *</label><input type="text" name="phone" id="phone" value="<?= e(posted('phone')) ?>" maxlength="20" required></div><div class="field"><label for="manager_employee_id">Manager</label><select name="manager_employee_id" id="manager_employee_id"><option value="">-- Not assigned --</option><?php foreach($managers as $m):?><option value="<?=e($m['employee_id'])?>" <?=posted('manager_employee_id')==(string)$m['employee_id']?'selected':''?>><?=e($m['full_name'])?></option><?php endforeach;?></select></div><div class="field"><label for="opening_date">Opening Date *</label><input type="date" name="opening_date" id="opening_date" value="<?= e(posted('opening_date')) ?>" required></div><div class="field"><label for="status">Status *</label><?php $current = posted('status'); ?><select name="status" id="status" required><option value="">-- Select --</option><?php foreach (enum_values('branch_status') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>