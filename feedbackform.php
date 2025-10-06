<!DOCTYPE html>
<html en>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feedback Page</title>

        <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #ff9a9e, #fecfef);
            color: #2c3e50;
            text-align: center;
        }

        h2 {
            color: #3498db;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            margin: 15px 0 5px;
            color: #3498db;
        }
        
        select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="radio"] {
            margin: 0 5px;
        }

        button {
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease-in-out;
        }

        button:hover {
            background: linear-gradient(to right, #45a049, #4CAF50);
        }

        .custom-dropdown-container {
            position: relative;
            margin-bottom: 15px;
        }

        .custom-dropdown {
            display: inline-block;
            width: 100%;
            text-align: left;
            position: relative;
        }

        .custom-dropdown-select {
            background: linear-gradient(to right, #e74c3c, #e67e22);
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .arrow-right {
            border: solid white;
            border-width: 0 3px 3px 0;
            display: inline-block;
            padding: 3px;
            transform: rotate(-45deg);
            margin-right: 5px;
        }

        .custom-dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            width: 100%;
            z-index: 1;
        }

        .custom-dropdown-content label {
            display: block;
            padding: 10px;
            cursor: pointer;
        }

        .custom-dropdown-content input[type="checkbox"] {
            margin-right: 5px;
        }

        .close-button-container {
            text-align: right;
            margin-top: 10px;
        }

        .close-button-1 {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: #fff;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease-in-out;
        }

        .close-button-1:hover {
            background: linear-gradient(to right, #2980b9, #3498db);
        }

        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 5px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 5px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
        
    </head>
    <body>      
        <h2>Feedback Form</h2>
        <form action="new_feedback.php" method="post">
            <?php
                $property_id = isset($_GET['property_id']) ? $_GET['property_id'] : '';
            ?>
            <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property_id); ?>">
            <h2>Which of the following types of provisions you think is necessary?</h2>
            <div class="custom-dropdown-container">
                <div class="custom-dropdown">
                    <div class="custom-dropdown-select" onclick="toggleDropdown('amenities')">
                        <span class="arrow-right"></span> Select provisions
                    </div>
                    <div class="custom-dropdown-content" id="amenities_content">
                        <label><input type="checkbox" name="apartment_amenities[]" value="Air-conditioner">Air-conditioner</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Washing machine">Washing machine</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Dryer">Dryer</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="External cloth-drying rack">External cloth-drying rack</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Built-in wardrobe">Built-in wardrobe</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Built-in kitchen cabinet">Built-in kitchen cabinet</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Cooking range">Cooking range</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Balcony lighting">Balcony lighting</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Built-in dishwasher">Built-in dishwasher</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Built-in kitchen cabinet">Built-in kitchen cabinet</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Built-in microwave">Built-in microwave</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Built-in oven">Built-in oven</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Built-in wardrobe in every bedroom">Built-in wardrobe in every bedroom</label>
                        <label><input type="checkbox" name="apartment_amenities[]" value="Others" onclick="handleOtherOption('amenities', this)">Others</label>
                        <div id="other_amenities" style="display: none;">
                            <label>Specify other amenities:</label>
                            <input type="text" name="apartment_amenities_other">
                        </div>
                        <div class="close-button-container">
                            <div class="close-button-1" onclick="closeDropdown('amenities')">Close</div>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Finishes - Kitchen</h3>
            <label for="kitchen_floor_finish">Select the type of floor finish you prefer:</label>
            <select name="kitchen_floor_finish" id="kitchen_floor_finish">
                <option value="Matte">Matte</option>
                <option value="Gloss">Gloss</option>
                <option value="Wood Finish">Wood Finish</option>
            </select>

            <label for="kitchen_wall_finish">Select the type of wall finish you prefer:</label>
            <select name="kitchen_wall_finish" id="kitchen_wall_finish">
                <option value="Matte">Matte</option>
                <option value="Gloss">Gloss</option>
                <option value="Wood Finish">Wood Finish</option>
            </select>

            <label id="kitchen_cabinet_color_label" for="kitchen_cabinet_color">Select the color of kitchen cabinet you prefer:</label>
            <input type="color" name="kitchen_cabinet_color" id="kitchen_cabinet_color" value="#ffffff">

            <h3>Finishes - Bathroom</h3>
            <label for="bathroom_floor_finish">Select the type of floor finish you prefer:</label>
            <select name="bathroom_floor_finish" id="bathroom_floor_finish">
                <option value="Matte">Matte</option>
                <option value="Gloss">Gloss</option>
                <option value="Wood Finish">Wood Finish</option>
            </select>

            <label for="bathroom_wall_finish">Select the type of wall finish you prefer:</label>
            <select name="bathroom_wall_finish" id="bathroom_wall_finish">
                <option value="Matte">Matte</option>
                <option value="Gloss">Gloss</option>
                <option value="Wood Finish">Wood Finish</option>
            </select>

            <label id="bathroom_cabinet_color_label" for="bathroom_cabinet_color">Select the color of bathroom cabinet you prefer:</label>
            <input type="color" id="bathroom_cabinet_color" name="bathroom_cabinet_color" value="#ffffff">

            <h3>Lean Premise Design</h3>
            <label for="opt_for_lpd">If there will be a 5% deduction in sale price, would you like to opt for LPD?</label>
            <input type="radio" name="opt_for_lpd" value="Yes"> Yes
            <input type="radio" name="opt_for_lpd" value="No"> No
            <br><br>

            <h2>Any additional feedback or comments?</h2>
            <textarea name="additional_feedback" rows="4" cols="50"></textarea>
            <button type="submit">Submit Feedback</button>
        </form>
        <script>
            // Function to close the dropdown content
            function closeDropdown(dropdownId) {
                const dropdownContent = document.getElementById(dropdownId + "_content");
                dropdownContent.style.display = "none";
            }

            function toggleDropdown(type) {
                // Toggle the selected dropdown and arrow icon
                const dropdownContent = document.getElementById(type + "_content");
                const dropdownSelect = dropdownContent.parentElement.querySelector(".custom-dropdown-select");
                if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                    dropdownSelect.classList.remove("expanded");
                } else {
                    dropdownContent.style.display = "block";
                    dropdownSelect.classList.add("expanded");
                }
            }


            function handleOtherOption(type, checkbox) {
                const otherOptions = document.getElementById("other_" + type);
                if (checkbox.checked) {
                    otherOptions.style.display = "block";
                    // If the "Other" option is selected, deselect "N/A"
                    const naOption = document.querySelector(`#${type}_content input[value="N/A"]`);
                    naOption.checked = false;
                } else {
                    otherOptions.style.display = "none";
                }
            }

            document.body.addEventListener("click", function (event) {
                const allDropdowns = document.querySelectorAll(".custom-dropdown-content");
                const dropdowns = document.querySelectorAll(".custom-dropdown");

                for (let i = 0; i < dropdowns.length; i++) {
                    if (event.target !== dropdowns[i] && !dropdowns[i].contains(event.target)) {
                        const dropdownContent = dropdowns[i].querySelector(".custom-dropdown-content");
                        const dropdownSelect = dropdowns[i].querySelector(".custom-dropdown-select");
                        dropdownContent.style.display = "none";
                        dropdownSelect.classList.remove("expanded");
                    }
                }
            });
        </script>
    </body>
</html>