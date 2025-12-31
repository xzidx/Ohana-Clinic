<?php
/*
Template Name: Doctor Schedule Page
*/
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

get_header();
?>

<div class="more-space"></div>

<div class="scheduler-card" id="scheduler">
    <div class="instruction">Please click on the date and time</div>

    <div class="controls">
        <div class="date-nav">
            <button class="nav-arrow" onclick="changeWeek(-1)">❮</button>
            <span id="dateRange"></span>
            <button class="nav-arrow" onclick="changeWeek(1)">❯</button>
        </div>

        <div class="toggle-group">
            <button class="toggle-btn" onclick="setView('day', this)">day</button>
            <button class="toggle-btn active" onclick="setView('week', this)">week</button>
        </div>
    </div>

    <div class="menu-filter">
        <button class="menu-btn active" onclick="filterMenu(this)">All Menu</button>
        <button class="menu-btn" onclick="filterMenu(this)">First Visit</button>
        <button class="menu-btn" onclick="filterMenu(this)">Follow-up Visit</button>
    </div>

    <div class="grid-container" id="gridHeader">
        <div class="header-day" style="background:#f1f5f9;">Time</div>
    </div>

    <div class="scroll-area">
        <div class="grid-container" id="gridBody"></div>
    </div>
</div>

<!-- Slot Modal -->
<div id="slotModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999; overflow:auto;">
    <div style="background:#fff; padding:20px; border-radius:10px; max-width:700px; width:95%; position:relative;">
        <span id="modalClose" style="position:absolute; top:10px; right:15px; cursor:pointer; font-weight:bold;">✖</span>
        <div id="modalContent"></div>
    </div>
</div>

<!-- Customer Info Modal -->
<div id="customerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:10000; overflow:auto;">
    <div style="background:#fff; padding:20px; border-radius:10px; max-width:700px; width:80%; position:relative;">
        <span id="customerClose" style="position:absolute; top:10px; right:15px; cursor:pointer; font-weight:bold;">✖</span>
        <div class="form-container">
            <h2>Customer Information</h2>
            <form id="customerForm">
                <input type="hidden" name="doctor_id" id="form-doctor-id">
                <input type="hidden" name="date" id="form-date">
                <input type="hidden" name="time" id="form-time">
                <input type="hidden" name="people" id="form-people">

                <div class="form-group">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last-name" placeholder="Enter Last Name" required>
                </div>

                <div class="form-group">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" name="first-name" placeholder="Enter First Name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Numbers only, no hyphens" pattern="[0-9]*" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                </div>

                <div class="form-group">
                    <label for="email-confirm">Email (Confirm)</label>
                    <input type="email" id="email-confirm" name="email-confirm" placeholder="Re-enter email" required>
                </div>

                <div class="form-group">
                    <label>Date of Birth</label>
                    <div class="dob-group">
                        <select id="birth-year" name="birth-year" required><option value="">Year</option></select>
                        <select id="birth-month" name="birth-month" required><option value="">Month</option></select>
                        <select id="birth-day" name="birth-day" required><option value="">Day</option></select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="patient-id">Patient ID / Chart #</label>
                    <input type="text" id="patient-id" name="patient-id" maxlength="20" placeholder="For returning patients only">
                </div>

                <button type="submit" class="submit-btn">Proceed to Confirmation</button>
            </form>
        </div>
    </div>
</div>

<?php
// Fetch all doctors
$doctors = [];
$query = new WP_Query([
    'post_type' => 'doctor',
    'posts_per_page' => -1,
]);
while ($query->have_posts()) {
    $query->the_post();
    $schedules = get_post_meta(get_the_ID(), '_ods_schedules', true);
    if (!is_array($schedules)) $schedules = [];
    $doctors[] = [
        'name' => get_the_title(),
        'id' => get_the_ID(),
        'schedules' => $schedules
    ];
}
wp_reset_postdata();
?>

<script>
const gridBody = document.getElementById('gridBody');
const gridHeader = document.getElementById('gridHeader');
const scheduler = document.getElementById('scheduler');
const dateRange = document.getElementById('dateRange');

const doctors = <?php echo json_encode($doctors); ?>;
let weekOffset = 0;
let currentFilter = 'all';
const MS_PER_DAY = 24*60*60*1000;

const slotModal = document.getElementById('slotModal');
const modalContent = document.getElementById('modalContent');
const customerModal = document.getElementById('customerModal');
const customerForm = document.getElementById('customerForm');

function getWeekStart(offset=0){
    const today = new Date();
    const diff = today.getDate() - today.getDay();
    const start = new Date(today.setDate(diff + offset*7));
    start.setHours(0,0,0,0);
    return start;
}

function fmt(d){
    return `${String(d.getMonth()+1).padStart(2,'0')}/${String(d.getDate()).padStart(2,'0')} ${['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d.getDay()]}`;
}

function renderHeader(){
    gridHeader.innerHTML = '<div class="header-day" style="background:#f1f5f9;">Time</div>';
    const start = getWeekStart(weekOffset);
    const end = new Date(start); end.setDate(start.getDate()+6);
    dateRange.textContent = `${start.getFullYear()}/${fmt(start)} ~ ${fmt(end)}`;

    for(let i=0;i<7;i++){
        const d = new Date(start); d.setDate(start.getDate() + i);
        const div = document.createElement('div'); div.className = `header-day d-${i+1}`;
        div.textContent = fmt(d);
        gridHeader.appendChild(div);
    }
}

function renderGrid(){
    gridBody.innerHTML='';
    const start = getWeekStart(weekOffset);

    for(let h=0; h<24; h++){
        const timeLabel = document.createElement('div');
        timeLabel.className = 'time-label';
        timeLabel.textContent = `${String(h).padStart(2,'0')}:00`;
        gridBody.appendChild(timeLabel);

        for(let d=0; d<7; d++){
            const slotDiv = document.createElement('div'); 
            slotDiv.className=`slot d-${d+1}`;
            slotDiv.style.display='flex';
            slotDiv.style.flexDirection='column';
            slotDiv.style.justifyContent='center';
            slotDiv.style.alignItems='center';
            slotDiv.style.textAlign='center';
            slotDiv.style.fontSize='12px';
            slotDiv.style.cursor='pointer';
            slotDiv.style.backgroundColor = '#eee';
            slotDiv.style.color = '#000';

            const cellDate = new Date(start.getTime() + d*MS_PER_DAY);
            const y = cellDate.getFullYear();
            const mon = String(cellDate.getMonth()+1).padStart(2,'0');
            const day = String(cellDate.getDate()).padStart(2,'0');
            const cellDateStr = `${y}-${mon}-${day}`;

            const slotStartMinutes = h*60;

            doctors.forEach(doc=>{
                doc.schedules.forEach(s=>{
                    const type = (s.type||'').toLowerCase();
                    if(s.date === cellDateStr && (currentFilter==='all' || currentFilter===type)){
                        const [startHour,startMin] = s.start.split(':').map(Number);
                        const [endHour,endMin] = s.end.split(':').map(Number);
                        const schedStart = startHour*60 + startMin;
                        const schedEnd = endHour*60 + endMin;

                        if(slotStartMinutes >= schedStart && slotStartMinutes < schedEnd){
                            slotDiv.innerHTML = `<strong>${doc.name}</strong><br><span style="font-size:10px;">${type==='first'?'First Visit':'Follow-up'}</span>`;
                            slotDiv.style.backgroundColor = s.full ? '#ff5555' : '#00ddff';
                            slotDiv.style.color = '#fff';

                            slotDiv.onclick = function(e){
                                e.stopPropagation();

                                const modal = slotModal;
                                const content = modalContent;

                                const visitType = type==='first'?'First Visit':'Follow-up Visit';
                                const startTime = `${String(startHour).padStart(2,'0')}:${String(startMin).padStart(2,'0')}`;
                                const endTime = `${String(endHour).padStart(2,'0')}:${String(endMin).padStart(2,'0')}`;
                                
                                const capacity = s.capacity || 1; // <-- FIX: define capacity

                                if(s.full){
                                    content.innerHTML = `
                                    <div class="container">
                                        <header>
                                            <h1>${visitType}</h1>
                                        </header>
                                        <div class="alert-box">
                                            We are very sorry, but<br>
                                            this service is fully booked for <strong>${capacity} people</strong>.
                                        </div>
                                        <section class="reservation-details">
                                            <h2>Reservation details</h2>
                                            <hr class="divider">
                                            <div class="info-grid">
                                                <div class="label">Date and time of use</div>
                                                <div class="value">
                                                    <strong>${cellDateStr} ${startTime} - ${endTime}</strong>
                                                    <span class="hint">Check the start and end dates</span>
                                                </div>
                                                <div class="label">Number of people <span class="required">Required</span></div>
                                                <div class="value">
                                                    <input type="number" value="${capacity}" min="1" class="input-field" disabled>
                                                </div>
                                            </div>
                                        </section>
                                        <section class="notes-section">
                                            <h3>Notes regarding reservations and cancellations</h3>
                                            <div class="policy-grid">
                                                <div class="label">Registration begins</div>
                                                <div class="value">Applications accepted from midnight 30 days prior</div>
                                                <div class="label">Application deadline</div>
                                                <div class="value">Reception up to 3 hours before</div>
                                                <div class="label">Cancellation deadline</div>
                                                <div class="value">Up to 3 hours before</div>
                                                <div class="label">Cancellation Policy</div>
                                                <div class="value">Contact the clinic directly</div>
                                            </div>
                                        </section>
                                        <div class="action-area">
                                            <button class="btn-primary disabled" disabled>make a reservation</button>
                                        </div>
                                    </div>`;
                                    modal.style.display = 'flex';
                                    return;
                                }

                                content.innerHTML = `
                                <div class="container">
                                    <header>
                                        <h1>${doc.name} - ${visitType}</h1>
                                        <p class="subtitle">Please check the reservation details below</p>
                                    </header>
                                    <section class="reservation-details">
                                        <h2>Reservation details</h2>
                                        <hr class="divider">
                                        <div class="info-grid">
                                            <div class="label">Date and time of use</div>
                                            <div class="value">
                                                <strong>${cellDateStr} ${startTime} - ${endTime}</strong>
                                                <span class="hint">Check the start and end dates</span>
                                            </div>
                                            <div class="label">Number of people <span class="required">Required</span></div>
                                            <div class="value">
                                                <input type="number" value="1" min="1" class="input-field" id="slot-people">
                                            </div>
                                        </div>
                                    </section>
                                    <section class="notes-section">
                                        <h3>Notes regarding reservations and cancellations</h3>
                                        <div class="policy-grid">
                                            <div class="label">Registration begins</div>
                                            <div class="value">Applications accepted from midnight 30 days prior</div>
                                            <div class="label">Application deadline</div>
                                            <div class="value">Reception up to 3 hours before</div>
                                            <div class="label">Cancellation deadline</div>
                                            <div class="value">Up to 3 hours before</div>
                                            <div class="label">Cancellation Policy</div>
                                            <div class="value">Contact the clinic directly</div>
                                        </div>
                                    </section>
                                    <div class="action-area">
                                        <button class="btn-primary" id="bookSlotBtn">Make a reservation</button>
                                    </div>
                                </div>`;

                                modal.style.display = 'flex';
                            };
                        }
                    }
                });
            });

            gridBody.appendChild(slotDiv);
        }
    }
}

// Close modals
document.getElementById('modalClose').onclick = function(){ slotModal.style.display = 'none'; };
document.getElementById('slotModal').onclick = function(e){ if(e.target === this) this.style.display = 'none'; };
document.getElementById('customerClose').onclick = function(){ customerModal.style.display = 'none'; };
document.getElementById('customerModal').onclick = function(e){ if(e.target === this) this.style.display = 'none'; };

function changeWeek(step){ weekOffset+=step; renderHeader(); renderGrid(); }
function filterMenu(btn){ 
    document.querySelectorAll('.menu-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const text = btn.textContent.toLowerCase();
    currentFilter = text.includes('first') ? 'first' : text.includes('follow') ? 'follow' : 'all';
    renderGrid();
}
function setView(view,btn){ 
    document.querySelectorAll('.toggle-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); 
    scheduler.classList.toggle('day-view',view==='day');
}

renderHeader();
renderGrid();

// Dynamic event delegation for "Make a reservation" button
document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'bookSlotBtn'){
        e.preventDefault();

        const slotContainer = e.target.closest('.container');
        const headerText = slotContainer.querySelector('h1').textContent;
        const doctorName = headerText.split(' - ')[0];
        const reservationInfo = slotContainer.querySelector('.reservation-details .info-grid .value strong').textContent;
        const [date, timeRange] = reservationInfo.split(' ');
        const time = timeRange.split('-')[0].trim();
        const people = slotContainer.querySelector('#slot-people').value;

        const doctor = doctors.find(d => d.name === doctorName);
        if(!doctor){
            alert("Doctor not found!");
            return;
        }

        document.getElementById('form-doctor-id').value = doctor.id;
        document.getElementById('form-date').value = date;
        document.getElementById('form-time').value = time;
        document.getElementById('form-people').value = people;

        slotModal.style.display = 'none';
        customerModal.style.display = 'flex';
    }
});

// AJAX submission for customer form
customerForm.onsubmit = function(e){
    e.preventDefault();
    const data = new FormData(customerForm);

    if(data.get('email') !== data.get('email-confirm')){
        alert("Email and confirmation do not match");
        return;
    }

    fetch("<?php echo admin_url('admin-ajax.php'); ?>?action=ods_book_slot", {
        method: "POST",
        body: data
    }).then(res=>res.json()).then(res=>{
        if(res.success){
            alert("Booking confirmed!");
            customerModal.style.display = 'none';
            customerForm.reset();
        } else {
            alert("Failed to book: "+res.data);
        }
    });
};

// Populate DOB dropdowns
const yearSelect = document.getElementById('birth-year');
const currentYear = new Date().getFullYear();
for (let i = currentYear; i >= 1920; i--) yearSelect.appendChild(new Option(i,i));

const monthSelect = document.getElementById('birth-month');
for (let i = 1; i <= 12; i++) monthSelect.appendChild(new Option(i,i));

const daySelect = document.getElementById('birth-day');
for (let i = 1; i <= 31; i++) daySelect.appendChild(new Option(i,i));
</script>

<?php get_footer(); ?>
