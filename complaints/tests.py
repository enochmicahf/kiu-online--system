from django.test import TestCase
from django.urls import reverse
from .models import User, Category, Complaint

class ComplaintSystemTests(TestCase):
    def setUp(self):
        self.student = User.objects.create_user(username='student1', password='password123', role='student')
        self.staff = User.objects.create_user(username='staff1', password='password123', role='staff')
        self.category = Category.objects.create(name='Academic', description='Academic issues')

    def test_registration(self):
        response = self.client.post(reverse('register'), {
            'username': 'newstudent',
            'password1': 'KIU_Student_2024!',
            'password2': 'KIU_Student_2024!'
        })
        self.assertEqual(response.status_code, 302)
        self.assertTrue(User.objects.filter(username='newstudent').exists())
        user = User.objects.get(username='newstudent')
        self.assertEqual(user.role, 'student')

    def test_complaint_submission(self):
        self.client.login(username='student1', password='password123')
        response = self.client.post(reverse('submit_complaint'), {
            'category': self.category.id,
            'title': 'Missing Grades',
            'description': 'I cannot see my grades for Semester 1.'
        })
        self.assertEqual(Complaint.objects.count(), 1)
        self.assertEqual(Complaint.objects.first().title, 'Missing Grades')

    def test_dashboard_access(self):
        self.client.login(username='student1', password='password123')
        response = self.client.get(reverse('dashboard'))
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'My Complaints')

    def test_staff_dashboard_access(self):
        self.client.login(username='staff1', password='password123')
        response = self.client.get(reverse('dashboard'))
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'Staff Dashboard')
