function showGlobalModal(contentUrl, params = {}, callback = null) {
  $('.modal-backdrop').remove();
  $('body').removeClass('modal-open').css('padding-right', '');

  $('#globalModalContent').html(`
    <div class="text-center p-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  `);

  const modal = new bootstrap.Modal(document.getElementById('globalModal'));
  modal.show();

  $.get(contentUrl, function (data) {
    try {
      // Clear any existing content first
      $('#globalModalContent').empty();
      
      // Normalize content: if a full modal is returned, extract its .modal-content
      const temp = document.createElement('div');
      temp.innerHTML = data;
      let injected = false;

      // Prefer a full modal wrapper -> extract inner modal-content
      const fullModal = temp.querySelector('.modal');
      if (fullModal) {
        const innerContent = fullModal.querySelector('.modal-content');
        if (innerContent) {
          // Clone the content to avoid moving DOM elements
          const clonedContent = innerContent.cloneNode(true);
          $('#globalModalContent').append(clonedContent);
          injected = true;
        }
      }

      // If no full modal wrapper, but modal-content exists, inject it
      if (!injected) {
        const modalContentOnly = temp.querySelector('.modal-content');
        if (modalContentOnly) {
          const clonedContent = modalContentOnly.cloneNode(true);
          $('#globalModalContent').append(clonedContent);
          injected = true;
        }
      }

      // Fallback: inject raw data
      if (!injected) {
        $('#globalModalContent').html(data);
      }

      // Store modal data for scripts to access
      window.modalData = params;

      // Execute any scripts included in fetched HTML so inline modal JS works
      try {
        const scripts = temp.querySelectorAll('script');
        scripts.forEach((script) => {
          const newScript = document.createElement('script');
          // copy attributes (e.g., type, src)
          [...script.attributes].forEach(attr => newScript.setAttribute(attr.name, attr.value));
          if (script.src) {
            newScript.src = script.src;
            newScript.async = false;
            document.body.appendChild(newScript);
          } else {
            newScript.textContent = script.textContent;
            document.body.appendChild(newScript);
          }
          // Remove the script after execution to avoid duplicates
          setTimeout(() => {
            try { newScript.remove(); } catch(e) {}
          }, 100);
        });
      } catch (e) {
        console.warn('Modal script execution warning:', e);
      }

      setTimeout(() => {
        try {
          if (typeof onGlobalModalReady === 'function') {
            onGlobalModalReady();
          }
          if (typeof callback === 'function') {
            callback();
          }
        } catch (callbackError) {
          console.error('Modal callback error:', callbackError);
        }
      }, 50);
    } catch (modalError) {
      console.error('Modal loading error:', modalError);
      $('#globalModalContent').html(`
        <div class="modal-header">
          <h5 class="modal-title text-danger">Error Loading Content</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Failed to load modal content. Please refresh the page and try again.
            <br><small class="text-muted">Error: ${modalError.message}</small>
          </div>
        </div>
      `);
    }
  }).fail(function(xhr, status, error) {
    console.error('AJAX Error:', {xhr, status, error});
    $('#globalModalContent').html(`
      <div class="modal-header">
        <h5 class="modal-title text-danger">Connection Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <i class="bi bi-wifi-off me-2"></i>
          Failed to load content. Please check your connection and try again.
          <br><small class="text-muted">Status: ${status} | Error: ${error}</small>
        </div>
      </div>
    `);
  });
}



function onGlobalModalReady() {
  console.log("✅ onGlobalModalReady triggered");

  const data = window.modalData;
  if (!data) {
    console.error("No modalData provided");
    return;
  }

  // Check if this is the booking modal
  if ($('#selected-service-name').length > 0) {
    // Populate service data
    $('#selected-service-name').text(data.name);
    $('#selected-service-price').text('₱' + data.price).data('price', data.price);
    $('#selected-service-id').val(data.id);
    
    // Store service data globally for therapist loading
    selectedServiceData = data;
    
    // Load therapists for this service
    if (typeof loadTherapists === 'function') {
      loadTherapists(data.id);
    }

    // Update confirm button handler
    $('#confirmServiceBtn').off('click').on('click', function () {
      const numPeople = parseInt($('#numPeople').val());

      if (numPeople < 1 || numPeople > 10) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Number',
          text: 'Please enter a valid number of people (1-10).',
        });
        return;
      }

      // Update selected therapists before proceeding
      if (typeof updateSelectedTherapists === 'function') {
        updateSelectedTherapists();
      }

      // Add service to cart with therapist information
      addServiceToCart(data, numPeople, selectedTherapists);
    });
  }
}

function addServiceToCart(serviceData, numPeople, therapists) {
  // Check for duplicates
  const existing = window.serviceCart.find(s => s.name === serviceData.name);
  if (existing) {
    Swal.fire({
      icon: 'info',
      title: 'Service Already Added',
      text: 'This service is already in your cart.',
    });
    return;
  }

  // Create service object with therapist data
  const serviceToAdd = {
    id: serviceData.id,
    name: serviceData.name,
    price: serviceData.price,
    people: numPeople,
    therapists: therapists || []
  };

  // Add to cart
  window.serviceCart.push(serviceToAdd);

  console.log('Service added with therapists:', serviceToAdd);

  // Visual feedback
  $('.service-card').removeClass('selected');
  if (window.selectedServiceElement) {
    window.selectedServiceElement.addClass('selected');
  }

  // Update checkout button
  if (typeof updateCheckoutBadge === 'function') {
    updateCheckoutBadge();
  }

  // Show success message
  Swal.fire({
    icon: 'success',
    title: 'Service Added!',
    text: `${serviceData.name} for ${numPeople} ${numPeople === 1 ? 'person' : 'people'} added to cart.`,
    timer: 1500,
    showConfirmButton: false
  });

  $('#globalModal').modal('hide');
}

// ✅ Make sure it's callable from globally injected modal
window.onGlobalModalReady = onGlobalModalReady;

// Legacy handler removed - now handled in onGlobalModalReady function
