/**
 * Form Accessibility Test Suite
 * Run this in Chrome DevTools Console to verify form accessibility
 */

console.log('🔍 Starting Form Accessibility Audit...\n');

// Test 1: Check for inputs without id
const inputsWithoutId = document.querySelectorAll('input:not([id]):not([type="hidden"])');
console.log(`✓ Test 1: Inputs without ID`);
console.log(`  Found: ${inputsWithoutId.length}`);
if (inputsWithoutId.length > 0) {
  console.warn('  ⚠️ Inputs missing ID:', inputsWithoutId);
} else {
  console.log('  ✅ All inputs have IDs');
}

// Test 2: Check for inputs without name
const inputsWithoutName = document.querySelectorAll('input:not([name]):not([type="hidden"]):not([type="button"]):not([type="submit"])');
console.log(`\n✓ Test 2: Inputs without name attribute`);
console.log(`  Found: ${inputsWithoutName.length}`);
if (inputsWithoutName.length > 0) {
  console.warn('  ⚠️ Inputs missing name:', inputsWithoutName);
} else {
  console.log('  ✅ All inputs have name attributes');
}

// Test 3: Check for textareas without id
const textareasWithoutId = document.querySelectorAll('textarea:not([id])');
console.log(`\n✓ Test 3: Textareas without ID`);
console.log(`  Found: ${textareasWithoutId.length}`);
if (textareasWithoutId.length > 0) {
  console.warn('  ⚠️ Textareas missing ID:', textareasWithoutId);
} else {
  console.log('  ✅ All textareas have IDs');
}

// Test 4: Check for textareas without name
const textareasWithoutName = document.querySelectorAll('textarea:not([name])');
console.log(`\n✓ Test 4: Textareas without name attribute`);
console.log(`  Found: ${textareasWithoutName.length}`);
if (textareasWithoutName.length > 0) {
  console.warn('  ⚠️ Textareas missing name:', textareasWithoutName);
} else {
  console.log('  ✅ All textareas have name attributes');
}

// Test 5: Check for selects without id
const selectsWithoutId = document.querySelectorAll('select:not([id])');
console.log(`\n✓ Test 5: Selects without ID`);
console.log(`  Found: ${selectsWithoutId.length}`);
if (selectsWithoutId.length > 0) {
  console.warn('  ⚠️ Selects missing ID:', selectsWithoutId);
} else {
  console.log('  ✅ All selects have IDs');
}

// Test 6: Check for selects without name
const selectsWithoutName = document.querySelectorAll('select:not([name])');
console.log(`\n✓ Test 6: Selects without name attribute`);
console.log(`  Found: ${selectsWithoutName.length}`);
if (selectsWithoutName.length > 0) {
  console.warn('  ⚠️ Selects missing name:', selectsWithoutName);
} else {
  console.log('  ✅ All selects have name attributes');
}

// Test 7: Check for labels without for attribute (excluding sr-only and decorative)
const labelsWithoutFor = Array.from(document.querySelectorAll('label:not([for])')).filter(
  label => !label.classList.contains('sr-only') && !label.querySelector('input, select, textarea')
);
console.log(`\n✓ Test 7: Labels without for attribute`);
console.log(`  Found: ${labelsWithoutFor.length}`);
if (labelsWithoutFor.length > 0) {
  console.warn('  ⚠️ Labels missing for attribute:', labelsWithoutFor);
} else {
  console.log('  ✅ All labels properly associated');
}

// Test 8: Check for orphaned labels (for attribute doesn't match any id)
const allLabels = document.querySelectorAll('label[for]');
const orphanedLabels = Array.from(allLabels).filter(label => {
  const forId = label.getAttribute('for');
  return !document.getElementById(forId);
});
console.log(`\n✓ Test 8: Orphaned labels (for attribute doesn't match any ID)`);
console.log(`  Found: ${orphanedLabels.length}`);
if (orphanedLabels.length > 0) {
  console.warn('  ⚠️ Orphaned labels:', orphanedLabels);
} else {
  console.log('  ✅ All labels point to valid inputs');
}

// Test 9: Check for duplicate IDs
const allIds = Array.from(document.querySelectorAll('[id]')).map(el => el.id);
const duplicateIds = allIds.filter((id, index) => allIds.indexOf(id) !== index);
console.log(`\n✓ Test 9: Duplicate IDs`);
console.log(`  Found: ${duplicateIds.length}`);
if (duplicateIds.length > 0) {
  console.warn('  ⚠️ Duplicate IDs found:', [...new Set(duplicateIds)]);
} else {
  console.log('  ✅ All IDs are unique');
}

// Test 10: Check for inputs with proper type attributes
const inputsWithoutType = document.querySelectorAll('input:not([type])');
console.log(`\n✓ Test 10: Inputs without type attribute`);
console.log(`  Found: ${inputsWithoutType.length}`);
if (inputsWithoutType.length > 0) {
  console.warn('  ⚠️ Inputs missing type:', inputsWithoutType);
} else {
  console.log('  ✅ All inputs have type attributes');
}

// Summary
console.log('\n' + '='.repeat(50));
console.log('📊 ACCESSIBILITY AUDIT SUMMARY');
console.log('='.repeat(50));

const totalIssues = 
  inputsWithoutId.length + 
  inputsWithoutName.length + 
  textareasWithoutId.length + 
  textareasWithoutName.length + 
  selectsWithoutId.length + 
  selectsWithoutName.length + 
  labelsWithoutFor.length + 
  orphanedLabels.length + 
  duplicateIds.length + 
  inputsWithoutType.length;

if (totalIssues === 0) {
  console.log('✅ PERFECT! No accessibility issues found.');
  console.log('✅ All forms are properly structured.');
  console.log('✅ Ready for production deployment.');
} else {
  console.warn(`⚠️ Found ${totalIssues} accessibility issue(s).`);
  console.warn('Please review the warnings above.');
}

console.log('='.repeat(50) + '\n');

// Return summary object
const summary = {
  inputsWithoutId: inputsWithoutId.length,
  inputsWithoutName: inputsWithoutName.length,
  textareasWithoutId: textareasWithoutId.length,
  textareasWithoutName: textareasWithoutName.length,
  selectsWithoutId: selectsWithoutId.length,
  selectsWithoutName: selectsWithoutName.length,
  labelsWithoutFor: labelsWithoutFor.length,
  orphanedLabels: orphanedLabels.length,
  duplicateIds: duplicateIds.length,
  inputsWithoutType: inputsWithoutType.length,
  totalIssues: totalIssues,
  passed: totalIssues === 0
};

console.log('📋 Detailed Summary:', summary);
console.log('\n💡 Tip: Open different modals/forms and run this test again to check all components.');
