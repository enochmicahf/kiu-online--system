from django.urls import path
from django.contrib.auth import views as auth_views
from . import views

urlpatterns = [
    path('', views.dashboard, name='dashboard'),
    path('register/', views.register, name='register'),
    path('login/', auth_views.LoginView.as_view(template_name='registration/login.html'), name='login'),
    path('logout/', auth_views.LogoutView.as_view(), name='logout'),
    path('submit/', views.submit_complaint, name='submit_complaint'),
    path('complaint/<int:pk>/', views.complaint_detail, name='complaint_detail'),
    path('notifications/', views.notifications_view, name='notifications'),
]
