const https = require('https');
const url = 'https://docs.google.com/spreadsheets/d/153-vC1sOgz3aS4O2R_MuGlTg1x3BzgGRfV_C5YsTMxk/export?format=csv&gid=0';

https.get(url, (res) => {
    if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        https.get(res.headers.location, (res2) => {
            let data = '';
            res2.on('data', chunk => data += chunk);
            res2.on('end', () => console.log(data));
        });
    } else {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => console.log(data));
    }
});
