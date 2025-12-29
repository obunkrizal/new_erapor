# Filament School Management System

A comprehensive school management system built with Laravel and Filament, designed specifically for Indonesian educational institutions. This application provides a modern, user-friendly admin panel for managing all aspects of school operations.

## Features

### Student Management (Siswa)
- Student enrollment and profile management
- Automatic NIS (Nomor Induk Siswa) generation based on academic year
- Student address management using Indonesian territory data
- Student status tracking (active, inactive, graduated)
- Photo upload and document management

### Teacher Management (Guru)
- Teacher profile and qualification management
- Employment status and position tracking
- Class assignment management
- Contact information and address management

### Class Management (Kelas)
- Class creation and configuration
- Teacher assignment to classes
- Student enrollment in classes
- Class capacity management
- Age range configuration for different educational levels

### Attendance Tracking (Absensi)
- Daily attendance recording
- Student attendance monitoring
- Attendance reports and analytics
- Automated attendance workflows

### School Fee Management (SPP)
- School fee structure configuration
- Payment tracking and management
- Invoice generation with barcodes
- Payment status monitoring
- Late payment notifications

### Academic Management
- Academic period management
- Grade recording and management
- Semester assessments
- Learning dimension tracking
- Achievement indicators

### Additional Features
- Indonesian territory integration for addresses
- Excel import/export functionality
- PDF report generation
- Image upload and processing
- Notification system
- User role management
- Audit logging

## Technologies Used

- **Laravel 12** - PHP web framework
- **Filament 4** - Admin panel builder
- **MySQL** - Database
- **Tailwind CSS** - Styling
- **Alpine.js** - JavaScript framework
- **Intervention Image** - Image processing
- **Laravel Excel** - Excel file handling
- **DomPDF** - PDF generation
- **Laravolt Indonesia** - Indonesian territory data
- **Barcode Generator** - Barcode creation

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL database
- Git

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd filamentSchool
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   - Create a MySQL database
   - Update `.env` file with database credentials
   - Run migrations and seeders
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

   For development with hot reload:
   ```bash
   composer run dev
   ```

## Usage

### Admin Panel Access
- Navigate to `/admin` in your browser
- Login with admin credentials
- Access various management modules through the sidebar

### Key Modules
- **Dashboard**: Overview of school statistics
- **Students**: Manage student information
- **Teachers**: Manage teacher profiles
- **Classes**: Configure classes and assignments
- **Attendance**: Record and monitor attendance
- **Payments**: Manage school fee payments
- **Reports**: Generate various reports

### Data Import/Export
- Use Excel templates for bulk data import
- Export student lists, attendance reports, and payment records
- PDF generation for official documents

## Database Structure

The application includes the following main entities:
- `users` - System users
- `siswas` - Students
- `gurus` - Teachers
- `kelas` - Classes
- `absensis` - Attendance records
- `pembayaran_spps` - School fee payments
- `nilais` - Grades
- `periodes` - Academic periods
- `sekolahs` - School information

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Security

If you discover any security vulnerabilities, please email the development team instead of creating a public issue.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
