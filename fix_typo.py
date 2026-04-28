import os

files = ['register.php', 'verify_biometric.php']

for filename in files:
    filepath = os.path.join(r'C:\xampp\htdocs\course-reg-portal', filename)
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Check and fix facingMode typo
    if 'facingMode' in content:
        content = content.replace('facingMode', 'facingMode')
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f'Fixed {filename}: facingMode -> facingMode')
    else:
        print(f'{filename}: facingMode is correct')
