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
    $('#globalModalContent').html(data);
    window.modalData = params;

    setTimeout(() => {
      if (typeof onGlobalModalReady === 'function') {
        onGlobalModalReady();
      }

      if (typeof callback === 'function') {
        callback(); // ✅ finally run it
      }
    }, 10);
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
