from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import login
from django.contrib.auth.decorators import login_required
from .forms import StudentRegistrationForm, ComplaintForm, FeedbackForm
from .models import Complaint, Notification

def register(request):
    if request.method == 'POST':
        form = StudentRegistrationForm(request.POST)
        if form.is_valid():
            user = form.save()
            login(request, user)
            return redirect('dashboard')
    else:
        form = StudentRegistrationForm()
    return render(request, 'registration/register.html', {'form': form})

@login_required
def dashboard(request):
    if request.user.role == 'student':
        complaints = request.user.complaints.all().order_by('-created_at')
        return render(request, 'complaints/student_dashboard.html', {'complaints': complaints})
    elif request.user.role in ['staff', 'admin']:
        complaints = Complaint.objects.all().order_by('-created_at')
        return render(request, 'complaints/staff_dashboard.html', {'complaints': complaints})
    return redirect('login')

@login_required
def submit_complaint(request):
    if request.user.role != 'student':
        return redirect('dashboard')
    if request.method == 'POST':
        form = ComplaintForm(request.POST, request.FILES)
        if form.is_valid():
            complaint = form.save(commit=False)
            complaint.student = request.user
            complaint.save()
            return redirect('dashboard')
    else:
        form = ComplaintForm()
    return render(request, 'complaints/submit_complaint.html', {'form': form})

@login_required
def complaint_detail(request, pk):
    complaint = get_object_or_404(Complaint, pk=pk)
    if request.user.role == 'student' and complaint.student != request.user:
        return redirect('dashboard')

    if request.method == 'POST' and request.user.role == 'student' and complaint.status == 'resolved':
        feedback_form = FeedbackForm(request.POST)
        if feedback_form.is_valid():
            feedback = feedback_form.save(commit=False)
            feedback.complaint = complaint
            feedback.save()
            return redirect('complaint_detail', pk=pk)
    else:
        feedback_form = FeedbackForm()

    return render(request, 'complaints/complaint_detail.html', {
        'complaint': complaint,
        'feedback_form': feedback_form
    })

@login_required
def notifications_view(request):
    notifications = request.user.notifications.all().order_by('-created_at')
    request.user.notifications.filter(is_read=False).update(is_read=True)
    return render(request, 'complaints/notifications.html', {'notifications': notifications})
