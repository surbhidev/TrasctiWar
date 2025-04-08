Project-1: TransactiWar – Battle for Security, Compete for Supremacy

Group-16

Work done by each member:
Pooja: -User Search & Money Transfer: Users can search other users by their username or user ID using SQL queries.
       -All transactions are recorded in the database, and a detailed transaction history is displayed to both the sender and receiver.
       -The backend logic is handled using PHP, while the frontend is designed using HTML, CSS, and JavaScript.
       -The system ensures data consistency and secure transactions by using SQL transactions and input validation.

       ~Script to create accounts automatically using Bash.
       ~It generates random usernames, secure passwords, and assigns an initial balance of 100 for each account, inserting them directly into the database using SQL commands.


Sohan : -Profile Management:Added a "View Profile" button: Each search result included a View Profile button that redirects the user to view_profile.php?id=<user_id>.
       -Allowed users to update their profile details
       -Updated the session data with the new profile information after successful updates.
 -Displayed the profile of a selected user. Fetched the user's details from the database using the user ID passed in the query string (e.g., view_profile.php?id=12).

SSL certificate: Created the necessary files and gave instructions to create it

Kummitha Jhanavi: ~User Activity Logging:Created a function to log user activity.Developed a reusable function logUserActivity to log user activity (e.g., accessed pages, IP address, timestamp).
	~ Database Table for Logs(id, username, webpage, timestamp, ip_address)


Surbhi: -I made the layout of the webpage, added the registration page, login page, user authentication and logout page using the HTML,CSS, PHP(backend).
	-Made the database named as Registration in that all the details of users(username, firstname, lastname, email, phoneno.,password)
	-The docker scripts to run my application inside the container.

STEPS TO RUN DOCKER:
-open docker desktop
-docker ps(to check the images)
-docker-compose up --build
-NOTE: if getting db connection error then run docker-compose down -v again run docker-compose up --build
-localhost:8080 (to run our webpage)
-localhost:8080/register.php (to register)
-localhost:8080/login.php (to login)
-localhost:8080/profile.php (to see our profile)
-localhost:8080/profile_edit.php (to edit profile)
-localhost:8080/money_transfer.php (to search. transfer and see transfer money history)

STEPS TO RUN create_accounts.sh (script to automatically register 10 users)
 bash create_accounts.sh
 Enter the full path to the PostgreSQL psql binary (e.g., /usr/bin/psql): /usr/bin/psql
 
Commands used for self signed certificate
mkdir ssl && cd ssl
openssl genrsa -out private.key 2048
openssl req -new -key private.key -out cert.csr
openssl x509 -req -days 365 -in cert.csr -signkey private.key -out certificate.crt


Run the following commands to generate the certificate and private key:

mkdir -p ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ssl/selfsigned.key -out ssl/selfsigned.crt


This will create two files in the ssl directory:

selfsigned.key: The private key.

selfsigned.crt: The self-signed certificate.

