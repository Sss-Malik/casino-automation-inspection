// Display a Bootstrap toast with the provided message.
const showToast = (message) => {
    const toastElement = document.getElementById("topcenter-Toast");
    const toastBody = toastElement.querySelector(".toast-body");
    if (toastBody) {
        toastBody.innerHTML = message;
    }
    new bootstrap.Toast(toastElement).show();
};

document.addEventListener("DOMContentLoaded", () => {
    // Shorthand for document.getElementById.
    const $ = (id) => document.getElementById(id);

    // Clear previous donation data and initialize state.
    localStorage.clear();
    let donationData = {};
    const totalSteps = 3;
    let currentStep = 1;

    // Update donation data in localStorage.
    const updateLocalStorage = () =>
        localStorage.setItem("donationData", JSON.stringify(donationData));

    // ---------------------- Google Places Autocomplete ----------------------
    const initAutocomplete = () => {
        const addressInput = document.getElementById("address-search");
            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                types: ["geocode"],
                componentRestrictions: { country: "uk" },
            });

            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace();
                donationData.address = place.formatted_address;
            });

    };

    // ---------------------- Phone Input Setup ----------------------
    const phoneInput = $("phone");
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "gb",
        separateDialCode: true,
    });

    // ---------------------- Utility Functions ----------------------
    // Toggle the display style of an element.
    const toggleDisplay = (element, displayStyle) => {
        element.style.display = displayStyle;
    };

    // Update the visible step and navigation tab.
    const updateStepTo = (targetStep) => {
        if (targetStep === currentStep) return;

        if (targetStep === 2) {
            initAutocomplete();
        }

        // On entering step 3, update phone data and handle donation type specifics.
        if (targetStep === 3) {
            console.log(JSON.parse(localStorage.getItem('donationData')));
            const fullNumber = iti.getNumber();
            $("fullPhoneNumber").value = fullNumber;
            donationData.phone = fullNumber;
            storeData();
            updateLocalStorage();
            $("donation_data").value = localStorage.getItem("donationData");

            // Disable bank transfer if donation is monthly.
            if (donationData.type === "Monthly") {
                const bankBtn = $("banktransferBtn");
                bankBtn.disabled = true;
                bankBtn.classList.add("disabled-button");
                bankBtn.setAttribute("data-bs-toggle", "tooltip");
                bankBtn.setAttribute("data-bs-custom-class", "tooltip-dark");
                bankBtn.setAttribute("data-bs-placement", "bottom");
                bankBtn.setAttribute(
                    "title",
                    "Bank transfer is only possible for one time payments."
                );
            }
        }

        // Hide current step and deactivate its tab.
        $(`step${currentStep}`).style.display = "none";
        $(`step${currentStep}-tab`).classList.remove("active");

        currentStep = targetStep;

        // Show new step and activate its tab.
        $(`step${currentStep}`).style.display = "block";
        $(`step${currentStep}-tab`).classList.add("active");

        // Update navigation buttons.
        $("nextBtn").style.display =
            currentStep === totalSteps ? "none" : "inline-block";
        $("backBtn").style.display = currentStep >= 1 ? "inline-block" : "none";
    };

    // Allow navigation to a target step only if previous steps are valid.
    const canNavigateToStep = (targetStep) => {
        for (let step = 1; step < targetStep; step++) {
            if (!validateStep(step)) return false;
        }
        return true;
    };

    // ---------------------- Navigation Handlers ----------------------
    $("nextBtn").addEventListener("click", () => {
        if (validateStep(currentStep) && currentStep < totalSteps) {
            updateStepTo(currentStep + 1);
        }
    });

    $("backBtn").addEventListener("click", () => {
        if (currentStep > 1) {
            updateStepTo(currentStep - 1);
        } else {
            // If at the first step, toggle back to the donation container.
            toggleDisplay($("waqf-donation-box"), "none");
            toggleDisplay($("donation-container"), "flex");
        }
    });

    // Tab click events.
    $("step1-tab").addEventListener("click", (e) => {
        e.preventDefault();
        updateStepTo(1);
    });
    $("step2-tab").addEventListener("click", (e) => {
        e.preventDefault();
        if (canNavigateToStep(2)) {
            updateStepTo(2);
        } else {
            showToast("Please complete previous steps before proceeding to Details.");
        }
    });
    $("step3-tab").addEventListener("click", (e) => {
        e.preventDefault();
        if (canNavigateToStep(3)) {
            updateStepTo(3);
        } else {
            showToast("Please complete the previous steps before proceeding to Payment.");
        }
    });

    // ---------------------- Donation Type and Payment Method ----------------------
    const setDonationType = (type) => {
        donationData.type = type;
        toggleDisplay($("waqf-donation-box"), "block");
        toggleDisplay($("donation-container"), "none");
    };

    $("oneoff").addEventListener("click", () => setDonationType("One-Off"));
    $("monthly").addEventListener("click", () => setDonationType("Monthly"));

    // Payment method setter.
    const setPaymentMethod = (paymentMethod) => {
        donationData.paymentMethod = paymentMethod;
        updateLocalStorage();
    };

    // ---------------------- Manual Address Toggle ----------------------
    $("waqf-manual-address").addEventListener("click", (e) => {
        e.preventDefault();
        const addressDiv = $("waqf_address");
        const isHidden =
            !addressDiv.style.display || addressDiv.style.display === "none";
        toggleDisplay(addressDiv, isHidden ? "flex" : "none");
    });

    // ---------------------- Input Field Updates ----------------------
    // Update donationData when an input loses focus.
    const updateInputValue = (input, key) => {
        input.addEventListener("blur", () => {
            donationData[key] = input.value;
            updateLocalStorage();
            console.log("Donation Data:", donationData);
        });
    };

    ["firstName", "lastName", "dateOfBirth", "email", "postcode", "city", "addressLine1", "addressLine2"].forEach((key) => {
        const inputElem = $(key);
        if (inputElem) updateInputValue(inputElem, key);
    });

    // ---------------------- Gift Aid Checkbox ----------------------
    $("giftAid").addEventListener("change", function () {
        donationData.giftAid = this.checked;
    });

    // ---------------------- Donation Amount ----------------------
    $("addAmountBtn").addEventListener("click", (e) => {
        e.preventDefault();
        document.querySelectorAll(".amount-option").forEach((btn) =>
            btn.classList.remove("active")
        );
        toggleDisplay($("addAmountBtn"), "none");
        toggleDisplay($("addAmountInput"), "block");
        $("addAmountInput").focus();
    });

    $("addAmountInput").addEventListener("blur", function () {
        const amountValue = this.value;
        $("selectedAmountInput").value = amountValue;
        donationData.amount = amountValue;
    });

    document.querySelectorAll(".amount-option").forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            document.querySelectorAll(".amount-option").forEach((btn) =>
                btn.classList.remove("active")
            );
            button.classList.add("active");
            const amount = button.getAttribute("data-amount");
            $("selectedAmountInput").value = amount;
            donationData.amount = amount;
            toggleDisplay($("addAmountInput"), "none");
            toggleDisplay($("addAmountBtn"), "block");
        });
    });

    // ---------------------- Taxpayer Radio Buttons ----------------------
    document.querySelectorAll("input[name='taxpayer']").forEach((radio) => {
        radio.addEventListener("change", function () {
            donationData.taxpayer = this.id === "yes";
        });
    });

    // ---------------------- Dropdown Setup ----------------------
    const setupDropdown = (inputId, dropdownClass) => {
        const input = $(inputId);
        const dropdown = document.querySelector(`.${dropdownClass}`);
        const items = dropdown.querySelectorAll("li");

        input.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleDisplay(dropdown, "block");
        });

        input.addEventListener("input", () => {
            const filter = input.value.toLowerCase();
            items.forEach((item) => {
                const itemText = item.getAttribute("data-value").toLowerCase();
                item.style.display = itemText.includes(filter) ? "block" : "none";
            });
        });

        items.forEach((item) => {
            item.addEventListener("click", () => {
                const selectedValue = item.getAttribute("data-value");
                input.value = item.textContent.trim();

                if (inputId === "waqf_causes") {
                    donationData.causeId = selectedValue;
                } else if (inputId === "country") {
                    donationData.country = selectedValue;
                } else if (inputId === "banks") {
                    donationData.bankId = selectedValue;
                }
                items.forEach((el) => (el.style.backgroundColor = "transparent"));
                item.style.backgroundColor = "#f0f8ff";
                toggleDisplay(dropdown, "none");
            });
        });

        document.addEventListener("click", (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                toggleDisplay(dropdown, "none");
            }
        });
    };

    setupDropdown("waqf_causes", "waqf_causes_list");
    setupDropdown("country", "country_list");
    setupDropdown("banks", "bank_list");

    // ---------------------- Payment Method Buttons ----------------------
    const paymentButtons = {
        banktransferBtn: {
            method: "Bank transfer",
            showContainer: "banktransferContainer",
            hideContainer: "bankSelectionContainer",
            deactivateBtn: "directDebitBtn",
        },
        directDebitBtn: {
            method: "Card",
            showContainer: "bankSelectionContainer",
            hideContainer: "banktransferContainer",
            deactivateBtn: "banktransferBtn",
        },
    };

    Object.entries(paymentButtons).forEach(([buttonId, config]) => {
        const button = $(buttonId);
        const showElement = $(config.showContainer);
        const hideElement = $(config.hideContainer);
        const deactivateElement = $(config.deactivateBtn);
        const navLinks = document.querySelectorAll(".nav-link");
        const backBtn = $("backBtn");
        const paymentSection = $("payment-section");

        button.addEventListener("click", () => {
            setPaymentMethod(config.method);
            const isHidden = showElement.style.display === "none";

            if (!isHidden) {
                button.classList.remove("active");
                navLinks.forEach((el) => el.classList.remove("disabled"));
                backBtn.disabled = false;
            } else {
                navLinks.forEach((el) => el.classList.add("disabled"));
                backBtn.disabled = true;
                button.classList.add("active");
            }

            // Show the payment section for card payments.
            const isCardPayment = config.method === "Card";
            if (isCardPayment) {
                paymentSection.style.display = "flex";
                paymentSection.classList.add("justify-content-center");
            }

            $("submitBtn").style.display = isHidden
                ? isCardPayment
                    ? "none"
                    : "block"
                : "none";
            $("stripeDonate").style.display = isHidden
                ? isCardPayment
                    ? "block"
                    : "none"
                : "none";

            hideElement.style.display = "none";
            deactivateElement.classList.remove("active");
            toggleDisplay(showElement, isHidden ? "block" : "none");
        });
    });

    // ---------------------- Phone Number Validation ----------------------
    phoneInput.addEventListener("blur", () => {
        if (!iti.isValidNumber()) {
            showToast("Please enter a valid phone number");
            return;
        }
        const fullNumber = iti.getNumber();
        $("fullPhoneNumber").value = fullNumber;
        donationData.phone = fullNumber;
        console.log("Donation Data:", donationData);
    });

    // ---------------------- Form Data Storage ----------------------
    const storeData = () => {
        donationData.amount = $("selectedAmountInput").value;
        donationData.giftAid = $("giftAid").checked;
        donationData.email = $("email").value;
        donationData.firstName = $("firstName").value;
        donationData.lastName = $("lastName").value;
        donationData.phone = $("fullPhoneNumber").value;
        donationData.dateOfBirth = $("dateOfBirth").value;
        donationData.address = $("address-search").value;

        const addressDiv = $("waqf_address");
        if (addressDiv && addressDiv.style.display !== "none") {
            donationData.addressLine1 = $("addressLine1").value;
            donationData.addressLine2 = $("addressLine2").value;
            donationData.postcode = $("postcode").value;
            donationData.city = $("city").value;
            donationData.country = $("country").value;
        }
    };

    // ---------------------- Form Submission ----------------------
    $("submitBtn").addEventListener("click", (e) => {
        e.preventDefault();
        if (!validateStep(currentStep)) return;
        storeData();
        updateLocalStorage();
        const donationDataJSON = localStorage.getItem("donationData");
        if (donationDataJSON) {
            $("donation_data").value = donationDataJSON;
        }
        document.querySelector(".payment-form").submit();
    });

    // ---------------------- Step Validation ----------------------
    const validateStep = (step) => {
        const getValue = (id) => $(id)?.value.trim();
        const showValidationError = (message) => {
            showToast(message);
            return false;
        };

        const validations = {
            1: [
                {
                    condition: !getValue("waqf_causes"),
                    message: "Please select a cause.",
                },
                {
                    condition: !getValue("selectedAmountInput"),
                    message: "Please select a donation amount.",
                },
                {
                    condition: !document.querySelector("input[name='taxpayer']:checked"),
                    message: "Please select whether you are a UK taxpayer.",
                },
            ],
            2: [
                {
                    condition: !getValue("firstName"),
                    message: "Please enter your first name.",
                },
                {
                    condition: !getValue("lastName"),
                    message: "Please enter your last name.",
                },
                {
                    condition: !getValue("email"),
                    message: "Please enter your email address.",
                },
                {
                    condition: !getValue("dateOfBirth"),
                    message: "Please enter your date of birth.",
                },
            ],
            3: [
                {
                    condition: !donationData.paymentMethod,
                    message: "Please select a payment method.",
                },
            ],
        };

        // Additional address validation for Step 2.
        if (step === 2) {
            const addressDiv = $("waqf_address");
            if (addressDiv && addressDiv.style.display !== "none") {
                validations[2].push(
                    {
                        condition: !getValue("addressLine1"),
                        message: "Please enter address line 1.",
                    },
                    {
                        condition: !getValue("postcode"),
                        message: "Please enter your postcode.",
                    },
                    {
                        condition: !getValue("city"),
                        message: "Please enter your city.",
                    },
                    {
                        condition: !getValue("country") && donationData.country,
                        message: "Please select your country from the dropdown.",
                    }
                );
            } else {
                validations[2].push({
                    condition: !getValue("address-search"),
                    message: "Please select your address or enter manually.",
                });
            }
        }

        // Additional payment validation for bank transfer in Step 3.
        if (step === 3 && donationData.paymentMethod === "Bank transfer") {
            validations[3].push({
                condition: !donationData.bankId,
                message: "Please select a bank for the bank transfer.",
            });
        }

        for (const { condition, message } of validations[step] || []) {
            if (condition) return showValidationError(message);
        }
        return true;
    };
});
