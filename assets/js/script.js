// Wait for the DOM to be fully loaded
document.addEventListener("DOMContentLoaded", () => {
  // Form validation
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (event) => {
      const requiredFields = form.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.classList.add("error")

          // Create error message if it doesn't exist
          let errorMessage = field.nextElementSibling
          if (!errorMessage || !errorMessage.classList.contains("error-message")) {
            errorMessage = document.createElement("div")
            errorMessage.classList.add("error-message")
            errorMessage.style.color = "red"
            errorMessage.style.fontSize = "0.8rem"
            errorMessage.style.marginTop = "0.25rem"
            field.parentNode.insertBefore(errorMessage, field.nextSibling)
          }

          errorMessage.textContent = "This field is required"
        } else {
          field.classList.remove("error")

          // Remove error message if it exists
          const errorMessage = field.nextElementSibling
          if (errorMessage && errorMessage.classList.contains("error-message")) {
            errorMessage.remove()
          }
        }
      })

      if (!isValid) {
        event.preventDefault()
      }
    })
  })

  // Password confirmation validation
  const passwordConfirmFields = document.querySelectorAll('input[name="confirm_password"]')
  passwordConfirmFields.forEach((field) => {
    field.addEventListener("input", function () {
      const passwordField = this.form.querySelector('input[name="password"]')
      if (passwordField && this.value !== passwordField.value) {
        this.setCustomValidity("Passwords do not match")
      } else {
        this.setCustomValidity("")
      }
    })
  })

  // Ticket quantity and price calculation
  const ticketQuantityInputs = document.querySelectorAll(".ticket-quantity")
  ticketQuantityInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const quantity = Number.parseInt(this.value)
      const price = Number.parseFloat(this.dataset.price)
      const totalElement = this.closest("form").querySelector(".ticket-total")

      if (totalElement && !isNaN(quantity) && !isNaN(price)) {
        const total = quantity * price
        totalElement.textContent = total.toFixed(2)

        // Update hidden total price input
        const totalInput = this.closest("form").querySelector('input[name="total_price"]')
        if (totalInput) {
          totalInput.value = total.toFixed(2)
        }
      }
    })
  })

  // Delete confirmation
  const deleteButtons = document.querySelectorAll(".delete-btn")
  deleteButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      if (!confirm("Are you sure you want to delete this item? This action cannot be undone.")) {
        event.preventDefault()
      }
    })
  })

  // Mobile navigation toggle
  const navToggle = document.querySelector(".nav-toggle")
  if (navToggle) {
    navToggle.addEventListener("click", () => {
      const nav = document.querySelector("nav ul")
      nav.classList.toggle("show")
    })
  }

  // Alert auto-dismiss
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.remove()
      }, 500)
    }, 5000)
  })
})
