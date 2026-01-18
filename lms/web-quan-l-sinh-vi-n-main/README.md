# 🎓 Student Management System (SMS)

A modern and responsive **Student Management System (SMS)** built using **PHP**, **MySQL**, and **Bootstrap 5**.  
This system provides an efficient and user-friendly way to manage student information — including adding, updating, deleting, and viewing records — all within a sleek interface.


---

## 🚀 Features

✅ **User Authentication**
- Secure login system with session-based access.
- Displays UI messages for invalid credentials.

✅ **Student Management**
- Add, update, and delete students easily.
- Clean Bootstrap modals for data input.

✅ **Search Functionality**
- Quick and easy search using a Bootstrap search bar.

✅ **Dark & Light Theme**
- Toggle between dark and light modes with a smooth switch.

✅ **Email Password Reset**
- Reset password securely via a password reset link sent to the user's email.



✅ **Backup and Restore**
- Export student data backups in CSV format to ensure data safety and prevent loss.
- Import and restore student data easily from CSV files within the system, enabling quick recovery.


✅ **Generate Student Report via PDF**
- Export and download detailed student reports in PDF format for easy sharing and printing.

✅ **Clean Alerts**
- Displays success and error messages for all actions with elegant UI design.

✅ **Modular File Structure**
- Organized PHP files and separate modal, JS, and CSS components for maintainability.


---



## 🧠 Technologies Used

| Technology   | Purpose                                              |
|--------------|------------------------------------------------------|
| PHP          | Backend scripting to handle server-side logic       |
| MySQL        | Database management for storing student information  |
| Bootstrap 5  | Frontend styling for responsive and modern UI       |
| HTML5 & CSS3 | Page structure and design with clean, semantic markup|
| JavaScript   | Adding interactivity and theme toggle functionality  |
| Gmail SMTP   | Sending secure password reset links via email        |


## ⚙️ Installation Guide

Follow these simple steps to get your Student Management System (SMS) up and running:

1. **Download the Project**
   - Clone the repository or download the ZIP file and extract it to your computer.

2. **Set Up Your Server Environment**
   - **Windows users:**
     Download and install [XAMPP](https://www.apachefriends.org/index.html).
     Move the project folder into the `htdocs` directory inside your XAMPP installation folder (e.g., `C:\xampp\htdocs\
student-management-system`).

   - **Linux users:**
     Install the [LAMP stack](https://bitnami.com/stack/lamp) on your machine.
     Place the project folder inside the web server root directory (usually `/opt/lampp/htdocs`).

3. **Configure the Database**
   - Open **phpMyAdmin** by navigating to [http://localhost/phpmyadmin](http://localhost/phpmyadmin) in your browser.

   - Create a new database (e.g., `sms_db`).
   - Import the provided SQL export file (usually named `database.sql` or similar) to set up the required tables and
 data.

4. **Update Configuration (if needed)**
   - Check the project’s configuration files (e.g., `config.php`) and update database credentials if necessary.

5. **Run the Application**
   - Open your browser and go to:
     `http://localhost/student-management-system`
   - You should see the Student Management System interface ready to use!



## 🤝 Contributing

Contributions are **warmly welcome**! Whether it's fixing bugs, adding new features, or improving documentation, your
 help makes this project better.

To contribute, please follow these steps:

1. **Fork** the repository to your own GitHub account.
2. **Create a new branch** for your feature or bugfix:
   ```bash
   git checkout -b feature/your-feature-name

3. **Make your changes** and commit them with clear messages:
   ```bash
   git commit -m "Add feature: brief description"

4. **Push** your branch to your forked repository:
   ```bash
   git push origin feature/your-feature-name

5. **Open a Pull Request** here on GitHub, describing your changes and why they should be merged.

Thank you for helping improve this project! 🎉



##  Author

**Ahmed Sahal**
University Lecturer, cybersecurity engineer & Developer
X [@bx1happy](https://twitter.com/bx1happy)
🌐 [https://github.com/cyberhappy](https://github.com/cyberhappy)

---

## ⭐ Show Your Support

If you find this project helpful, please ⭐ star this repository on GitHub — it encourages more updates and improvements
!

## 🧾 License

This project is licensed under the GNU General Public License v3.0 (GPL-3.0).
You may use, modify, and distribute this software freely as long as the same license is applied.

