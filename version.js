/**
 * Script to update version.php with the current date and get the current version from the package.json
 */
fs = require('fs');
fs.readFile('package.json', 'utf8', (err, data) => {
    if (err) {
        throw err;
    }
    let package = JSON.parse(data);
    let date = new Date();
    fs.writeFile(
        'package.php',
        `<?php // Generated on ${date}
define('VERSION', '${package.version}');
define('DATE', '${date.toDateString()}');
`,
        null,
        (err) => {
            if (err) {
                throw err;
            }
        });
});

