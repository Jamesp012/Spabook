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

  $('#selected-service-name').text(data.name);
  $('#selected-service-price').text('₱' + data.price).data('price', data.price);
  $('#selected-service-id').val(data.id);

  $('#confirmServiceBtn').off('click').on('click', function () {
    const numPeople = $('#numPeople').val();

    if (numPeople < 1) {
      alert('Please enter a valid number.');
      return;
    }

    $('.service-card').removeClass('selected');
    if (window.selectedServiceElement) {
      window.selectedServiceElement.addClass('selected');
    }

    $('#globalModal').modal('hide');
  });
}

// ✅ Make sure it's callable from globally injected modal
window.onGlobalModalReady = onGlobalModalReady;

$(document).on('click', '#confirmServiceBtn', function () {
  const serviceName = $('#selected-service-name').text();
  const servicePrice = $('#selected-service-price').data('price');
  const serviceId = $('#selected-service-id').val();
  const numPeople = parseInt($('#numPeople').val());

  if (isNaN(numPeople) || numPeople < 1) {
    alert('Please enter a valid number of people.');
    return;
  }

  // Prevent duplicates based on name
  const existing = serviceCart.find(s => s.name === serviceName);
  if (!existing) {
    serviceCart.push({
      id: serviceId,
      name: serviceName,
      price: servicePrice,
      people: numPeople
    });

    updateCheckoutBadge();
    $('#globalModal').modal('hide');
  } else {
    alert("This service is already added.");
  }
});
