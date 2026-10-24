# Academic CRUD — Render + Aiven deployment

This is a PHP/MySQL application. Render hosts the PHP app; Aiven hosts the MySQL database. The SQL dump is imported into Aiven separately.

## 1. Create the Aiven MySQL database
1. Create a MySQL service in Aiven.
2. Open the service's connection information and copy the host, port, database name, and username. Do not use `localhost` for Aiven.
3. Download the CA certificate from the Aiven service's connection/security page.
4. In Aiven, allow connections from Render. If you use Aiven's IP allowlist, add the outbound IP ranges applicable to your Render service/plan, or use a supported private networking option.

## 2. Import `academic_db.sql` into Aiven
Use Aiven's query console or a MySQL client such as MySQL Workbench. Connect with the Aiven host/port/user and TLS enabled, then import `academic_db.sql`.
- If the database created by Aiven has a different name, either create/use `academic_db` if permitted or change `DB_NAME` and remove/adjust the SQL dump's database comments as needed.
- The SQL dump creates tables but does not contain `CREATE DATABASE`; select the target database before importing.
- Confirm `tbl_users`, `tbl_usertype`, and the other tables exist after import.

## 3. Push this folder to GitHub
Upload the contents of the `academic_crud` folder (including `Dockerfile` and `render.yaml`) to a GitHub repository. Do not commit `.env` files, passwords, or private credentials.

## 4. Deploy on Render
1. In Render, choose **New + → Blueprint** and select the repository containing `render.yaml`, or create a Docker Web Service from the repository.
2. Set these environment variables in the Render service:
   - `DB_HOST`: Aiven hostname
   - `DB_PORT`: Aiven port (not necessarily 3306)
   - `DB_NAME`: database/schema name containing the imported tables
   - `DB_USER`: Aiven username
   - `DB_PASSWORD`: Aiven password
   - `DB_SSL_CA`: `/etc/secrets/ca.pem`
3. Add the Aiven CA certificate as a **Secret File** in Render with filename `ca.pem`. Render mounts it at `/etc/secrets/ca.pem`.
4. Deploy. Visit `/health.php`; it should display `OK`. Then open the service URL and test login and CRUD pages.

## 5. Important notes
- The local XAMPP defaults still work when no `DB_*` environment variables are set.
- The database password is not stored in the PHP source.
- Render's normal container filesystem is ephemeral. Uploaded profile/instructor images may disappear on redeploy/restart. For persistent uploads, configure a persistent disk mounted at a suitable path and update the image directories, or move uploads to object storage.
- The application currently includes sample user accounts from the SQL dump. Change/remove demo accounts and use strong passwords before sharing the public deployment.
- Never expose Aiven credentials or commit the CA/private files to a public repository.
