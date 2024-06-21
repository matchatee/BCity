<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Management</title>
    <style>
        /* Basic styles for tabs */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }

        .tab button:hover {
            background-color: #ddd;
        }

        .tab button.active {
            background-color: #ccc;
        }

        .tabcontent {
            display: none;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-top: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .centered {
            text-align: center;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
    window.onload = function() {
        console.log("Window loaded");

        window.openTab = function(tabName) {
            console.log("Opening tab: " + tabName);
            var i, tabcontent, tablinks;

            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            document.getElementById(tabName).style.display = "block";
            // Find the button for this tab and add the active class
            for (i = 0; i < tablinks.length; i++) {
                if (tablinks[i].textContent.trim() === tabName.replace(/([A-Z])/g, ' $1').trim()) {
                    tablinks[i].className += " active";
                    break;
                }
            }
        }

        window.showContactDetails = function(clientId) {
            console.log("Showing contact details for client ID: " + clientId);
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("ContactDetailsModal").style.display = "block";
                    document.getElementById("contactDetailsContent").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "get_client_contacts.php?clientId=" + clientId, true);
            xhttp.send();
        }

        window.closeContactDetails = function() {
            console.log("Closing contact details");
            document.getElementById("ContactDetailsModal").style.display = "none";
        }

        // Function to add a new contact
        window.addNewContact = function() {
            var lastName = document.getElementById('newContactLastName').value;
            var name = document.getElementById('newContactName').value;
            var email = document.getElementById('newContactEmail').value;

            // Example of form validation (you should expand this)
            if (!lastName || !name || !email) {
                alert("Please fill in all fields.");
                return;
            }

            // Prepare data for AJAX submission
            var formData = {
                lastName: lastName,
                name: name,
                email: email
            };

            // Simulate AJAX request for demonstration
            console.log("Adding new contact:", formData);

            // Clear form after submission (optional)
            document.getElementById('addContactForm').reset();
        }

        // Open the default tab
        openTab('NewClient');
    }
    </script>
</head>
<body>

    <h1>Client Management</h1>

    <div class="tab">
        <button class="tablinks" onclick="openTab('NewClient')">New Client</button>
        <button class="tablinks" onclick="openTab('ClientList')">Client List</button>
        <button class="tablinks" onclick="openTab('ContactList')">Contact List</button>
        <button class="tablinks" onclick="openTab('AddContact')">Add Contact</button>
    </div>

    <div id="NewClient" class="tabcontent">
        <h2>Create New Client</h2>
        <form action="create_client.php" method="post">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required><br><br>

            <label for="num_contacts">Number of Linked Contacts:</label><br>
            <input type="number" id="num_contacts" name="num_contacts" value="0" required><br><br>

            <input type="submit" value="Create Client">
        </form>
    </div>

    <div id="ClientList" class="tabcontent">
        <?php
        // Enable error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Database connection settings
        $servername = "localhost";
        $username = "root";
        $password = "Tino";
        $dbname = "client_management";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch clients from the database ordered by name
        $sql = "SELECT id, name, client_code, num_contacts FROM clients ORDER BY name ASC";
        $result = $conn->query($sql);

        if ($result === false) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        } else {
            if ($result->num_rows > 0) {
                echo "<h2>Client List</h2>";
                echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Client Code</th><th class='centered'>Number of Contacts</th><th>Action</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["client_code"]) . "</td>";
                    echo "<td class='centered'>" . $row["num_contacts"] . "</td>";
                    echo "<td><button onclick='showContactDetails(" . $row["id"] . ")'>Show Contact Details</button></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "No client(s) found";
            }
        }

        $conn->close();
        ?>
    </div>

    <div id="ContactList" class="tabcontent">
        <?php
        // Database connection settings (repeated for demonstration)
        $servername = "localhost";
        $username = "root";
        $password = "Tino";
        $dbname = "client_management";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch contacts from the database
        $sql = "SELECT id, last_name, name, email FROM contacts ORDER BY last_name ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<h2>Contact List</h2>";
            echo "<table border='1'><tr><th>ID</th><th>Last Name</th><th>Name</th><th>Email</th>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . htmlspecialchars($row["last_name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No contacts found";
        }

        $conn->close();
        ?>
    </div>

    <div id="AddContact" class="tabcontent">
        <h2>Add New Contact</h2>
        <form id="addContactForm" onsubmit="event.preventDefault(); addNewContact();">
            <label for="newContactLastName">Last Name:</label><br>
            <input type="text" id="newContactLastName" name="newContactLastName" required><br><br>

            <label for="newContactName">Name:</label><br>
            <input type="text" id="newContactName" name="newContactName" required><br><br>

            <label for="newContactEmail">Email:</label><br>
            <input type="email" id="newContactEmail" name="newContactEmail" required><br><br>

            <input type="submit" value="Add Contact">
        </form>
    </div>

    <div id="ContactDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeContactDetails()">&times;</span>
            <h2>Contact Details</h2>
            <div id="contactDetailsContent"></div>
        </div>
    </div>

    <script>
    window.onload = function() {
        console.log("Window loaded");

        window.openTab = function(tabName) {
            console.log("Opening tab: " + tabName);
            var i, tabcontent, tablinks;

            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            document.getElementById(tabName).style.display = "block";
            // Find the button for this tab and add the active class
            for (i = 0; i < tablinks.length; i++) {
                if (tablinks[i].textContent.trim() === tabName.replace(/([A-Z])/g, ' $1').trim()) {
                    tablinks[i].className += " active";
                    break;
                }
            }
        }

        window.showContactDetails = function(clientId) {
            console.log("Showing contact details for client ID: " + clientId);
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("ContactDetailsModal").style.display = "block";
                    document.getElementById("contactDetailsContent").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "get_client_contacts.php?clientId=" + clientId, true);
            xhttp.send();
        }

        window.closeContactDetails = function() {
            console.log("Closing contact details");
            document.getElementById("ContactDetailsModal").style.display = "none";
        }

        // Function to add a new contact
       // Function to add a new contact
window.addNewContact = function() {
    var lastName = document.getElementById('newContactLastName').value;
    var name = document.getElementById('newContactName').value;
    var email = document.getElementById('newContactEmail').value;

    // Example of form validation (you should expand this)
    if (!lastName || !name || !email) {
        alert("Please fill in all fields.");
        return;
    }

    // Prepare data for AJAX submission
    var formData = {
        lastName: lastName,
        name: name,
        email: email
    };

    // Send AJAX request to add contact
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                console.log('Contact added successfully:', this.responseText);
                // Optionally update UI or display success message
                // Reload or refresh the contact list tab
                openTab('ContactList');
            } else {
                console.error('Error adding contact:', this.statusText);
                // Handle error scenario
            }
        }
    };
    xhttp.open("POST", "add_contact.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("lastName=" + encodeURIComponent(lastName) + "&name=" + encodeURIComponent(name) + "&email=" + encodeURIComponent(email));
    
    // Clear form after submission (optional)
    document.getElementById('addContactForm').reset();
}

        // Open the default tab
        openTab('NewClient');
    }
    </script>

</body>
</html>

