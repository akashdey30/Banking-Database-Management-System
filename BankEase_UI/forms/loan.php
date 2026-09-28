<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Loan';
$base = '..';
$errors = [];
if(is_post()){
 try{
 $customer=(int)posted('customer_id');$type=posted('loan_type');$principal=posted('principal_amount');$rate=posted('interest_rate');$duration=posted('duration_months');$app=posted('application_date');$approval=posted_or_null('approval_date');$account=posted_or_null('disbursement_account_id');$monthly=posted('monthly_installment');$status=posted('status');
 foreach(['customer_id','loan_type','principal_amount','interest_rate','duration_months','application_date','monthly_installment','status'] as $x)if(posted($x)==='')$errors[]=ucwords(str_replace('_',' ',$x)).' is required.';
 if(!is_numeric($principal)||$principal<=0)$errors[]='Principal amount must be greater than zero.';if(!is_numeric($rate)||$rate<0)$errors[]='Interest rate must be zero or greater.';if(!ctype_digit($duration)||$duration<=0)$errors[]='Duration must be a positive whole number.';if(!is_numeric($monthly)||$monthly<0)$errors[]='Monthly installment must be zero or greater.';
 if(!in_array($status,enum_values('loan_status'),true))$errors[]='Invalid loan status.';
 if(!$errors){$outstanding=$principal;$approvedBy=($status==='approved'||$status==='active')?DEFAULT_USER_ID:null;
 db_insert('INSERT INTO loans(customer_id,loan_type,principal_amount,interest_rate,duration_months,application_date,approval_date,disbursement_account_id,monthly_installment,outstanding_balance,status,approved_by_user_id) VALUES(:c,:t,:p,:r,:d,:app,:ap,:acc,:m,:o,:s,:u)',
 [':c'=>$customer,':t'=>$type,':p'=>$principal,':r'=>$rate,':d'=>$duration,':app'=>$app,':ap'=>$approval,':acc'=>$account? (int)$account:null,':m'=>$monthly,':o'=>$outstanding,':s'=>$status,':u'=>$approvedBy]);
 flash_set('success','Loan added successfully.');redirect('loan.php');}
 }catch(Throwable $e){$errors[]=exception_message($e);}}

include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label>Customer *</label><?php
$opts = db_select_all("SELECT customer_id,CONCAT(cif_number,' - ',full_name) AS label FROM customers ORDER BY full_name");
$current = posted('customer_id');
?><select name="customer_id" id="customer_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['customer_id']) ?>" <?= (string)$current === (string)$opt['customer_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label for="loan_type">Loan Type *</label><input type="text" name="loan_type" id="loan_type" value="<?= e(posted('loan_type')) ?>" maxlength="50" required></div><div class="field"><label for="principal_amount">Principal Amount *</label><input type="number" name="principal_amount" id="principal_amount" value="<?= e(posted('principal_amount')) ?>" step="0.01" min="0.01" required></div><div class="field"><label for="interest_rate">Interest Rate (%) *</label><input type="number" name="interest_rate" id="interest_rate" value="<?= e(posted('interest_rate')) ?>" step="0.01" min="0" required></div><div class="field"><label for="duration_months">Duration (Months) *</label><input type="number" name="duration_months" id="duration_months" value="<?= e(posted('duration_months')) ?>" min="1" required></div><div class="field"><label for="application_date">Application Date *</label><input type="date" name="application_date" id="application_date" value="<?= e(posted('application_date')) ?>" required></div><div class="field"><label for="approval_date">Approval Date</label><input type="date" name="approval_date" id="approval_date" value="<?= e(posted('approval_date')) ?>"></div><div class="field"><label>Disbursement Account</label><?php
$opts = db_select_all("SELECT account_id,CONCAT(account_number,' - ',account_title) AS label FROM accounts ORDER BY account_number");
$current = posted('disbursement_account_id');
?><select name="disbursement_account_id" id="disbursement_account_id"><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['account_id']) ?>" <?= (string)$current === (string)$opt['account_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label for="monthly_installment">Monthly Installment *</label><input type="number" name="monthly_installment" id="monthly_installment" value="<?= e(posted('monthly_installment')) ?>" step="0.01" min="0" required></div><div class="field"><label>Status *</label><?php $current = posted('status'); ?><select name="status" id="status" required><option value="">-- Select --</option><?php foreach (enum_values('loan_status') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>