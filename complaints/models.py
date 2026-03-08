from django.db import models
from django.contrib.auth.models import AbstractUser

class User(AbstractUser):
    ROLE_CHOICES = (
        ('student', 'Student'),
        ('staff', 'Staff'),
        ('admin', 'Administrator'),
    )
    role = models.CharField(max_length=10, choices=ROLE_CHOICES, default='student')

class Category(models.Model):
    name = models.CharField(max_length=100)
    description = models.TextField(blank=True)

    def __str__(self):
        return self.name

    class Meta:
        verbose_name_plural = "Categories"

class Complaint(models.Model):
    STATUS_CHOICES = (
        ('pending', 'Pending'),
        ('in_progress', 'In Progress'),
        ('resolved', 'Resolved'),
        ('closed', 'Closed'),
    )
    student = models.ForeignKey(User, on_delete=models.CASCADE, related_name='complaints', limit_choices_to={'role': 'student'})
    category = models.ForeignKey(Category, on_delete=models.SET_NULL, null=True, related_name='complaints')
    title = models.CharField(max_length=200)
    description = models.TextField()
    attachment = models.FileField(upload_to='complaints/attachments/', blank=True, null=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    assigned_to = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, blank=True, related_name='assigned_complaints', limit_choices_to={'role': 'staff'})

    def __str__(self):
        return f"{self.title} - {self.student.username}"

class Feedback(models.Model):
    complaint = models.OneToOneField(Complaint, on_delete=models.CASCADE, related_name='feedback')
    rating = models.PositiveSmallIntegerField(choices=[(i, str(i)) for i in range(1, 6)])
    comment = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"Feedback for {self.complaint.title}"

class Notification(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='notifications')
    message = models.TextField()
    is_read = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"Notification for {self.user.username}"

from django.db.models.signals import post_save, pre_save
from django.dispatch import receiver

@receiver(pre_save, sender=Complaint)
def capture_old_status(sender, instance, **kwargs):
    if instance.pk:
        instance._old_status = Complaint.objects.get(pk=instance.pk).status
    else:
        instance._old_status = None

@receiver(post_save, sender=Complaint)
def create_complaint_notification(sender, instance, created, **kwargs):
    if created:
        # Notify staff
        staff_users = User.objects.filter(role='staff')
        for staff in staff_users:
            Notification.objects.create(
                user=staff,
                message=f"New complaint submitted: {instance.title}"
            )
    else:
        # Notify student about status change only if it actually changed
        old_status = getattr(instance, '_old_status', None)
        if old_status and old_status != instance.status:
            Notification.objects.create(
                user=instance.student,
                message=f"Your complaint '{instance.title}' status has been updated to {instance.get_status_display()}."
            )
