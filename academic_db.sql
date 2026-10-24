-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 07:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `academic_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_course`
--

CREATE TABLE `tbl_course` (
  `ID` int(11) NOT NULL,
  `COURSE` varchar(50) NOT NULL,
  `DESCRIPTION` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_course`
--

INSERT INTO `tbl_course` (`ID`, `COURSE`, `DESCRIPTION`) VALUES
(2, 'BSIS', 'Bachelor of Science in Information Systems'),
(3, 'BSTM', 'Bachelor of Science in Tourism Management'),
(4, 'BSA', 'Bachelor of Science in Accountancy'),
(5, 'BSCrim', 'Bachelor of Science in Criminology'),
(6, 'BSCpe', 'Bachelor of Science in Computer Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_instructors`
--

CREATE TABLE `tbl_instructors` (
  `ID` int(11) NOT NULL,
  `SALUTATION` varchar(20) NOT NULL,
  `FIRSTNAME` varchar(100) NOT NULL,
  `MIDDLENAME` varchar(100) NOT NULL DEFAULT '',
  `LASTNAME` varchar(100) NOT NULL,
  `PHOTO` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_instructors`
--

INSERT INTO `tbl_instructors` (`ID`, `SALUTATION`, `FIRSTNAME`, `MIDDLENAME`, `LASTNAME`, `PHOTO`) VALUES
(1, 'Mr.', 'Gregorio', 'Hilario', 'Del Pilar', '625252014a46c941e243d51349655ca3.jpg'),
(2, 'Engr.', 'Emilio', 'Famy', 'Aguinaldo', 'ecbcdf9de592f1c962daf6c259de2de4.jpg'),
(3, 'Prof.', 'Antonio', 'heneral', 'Luna', '587e6bd6009d9bf086169abe7f62f7d6.webp');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_schoolyear`
--

CREATE TABLE `tbl_schoolyear` (
  `ID` int(11) NOT NULL,
  `SCHOOLYEAR` varchar(20) NOT NULL,
  `DESCRIPTION` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_schoolyear`
--

INSERT INTO `tbl_schoolyear` (`ID`, `SCHOOLYEAR`, `DESCRIPTION`) VALUES
(1, '2023-2024', 'School Year 2023-2024'),
(2, '2024-2025', 'School Year 2024-2025'),
(3, '2025-2026', 'School Year 2025-2026'),
(4, '2026-2027', 'School Year 2026-2027');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_section`
--

CREATE TABLE `tbl_section` (
  `ID` int(11) NOT NULL,
  `SECTION` varchar(30) NOT NULL,
  `DESCRIPTION` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_section`
--

INSERT INTO `tbl_section` (`ID`, `SECTION`, `DESCRIPTION`) VALUES
(1, 'A', 'Section A'),
(2, 'B', 'Section B'),
(3, 'C', 'Section C'),
(4, 'D', 'Section D'),
(5, 'E', 'Section E');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_subjects`
--

CREATE TABLE `tbl_subjects` (
  `ID` int(11) NOT NULL,
  `COURSE` varchar(50) NOT NULL,
  `YEARLEVEL` varchar(20) NOT NULL,
  `TERM` varchar(30) NOT NULL,
  `CODE` varchar(50) NOT NULL,
  `TITLE` varchar(150) NOT NULL,
  `LECTURE` decimal(5,2) NOT NULL,
  `LABORATORY` decimal(5,2) NOT NULL,
  `CREDIT` decimal(5,2) NOT NULL,
  `GRADE` decimal(5,2) NOT NULL,
  `PRE_REQUISITE` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_subjects`
--

INSERT INTO `tbl_subjects` (`ID`, `COURSE`, `YEARLEVEL`, `TERM`, `CODE`, `TITLE`, `LECTURE`, `LABORATORY`, `CREDIT`, `GRADE`, `PRE_REQUISITE`) VALUES
(1, 'BSIS', '1ST', '1ST SEM', 'CC101', 'Introduction to Computing', 3.00, 0.00, 3.00, 0.00, 'NONE'),
(2, 'BSIS', '1ST', '2ND SEM', 'CC103', 'Computer Programming 2', 2.00, 1.00, 3.00, 0.00, 'NONE'),
(3, 'BSIS', '2ND', '1ST SEM', 'DM102', 'Financial Management', 3.00, 0.00, 3.00, 0.00, 'NONE'),
(4, 'BSIS', '2ND', '2ND SEM', 'MATH2', 'Discrete Mathematics', 3.00, 2.00, 3.00, 0.00, 'MATH1'),
(5, 'BSIS', '3RD', '1ST SEM', 'ADV01', 'IS Elective 1', 2.00, 1.00, 3.00, 0.00, '3rd Yr. Standing'),
(6, 'BSIS', '3RD', '2ND SEM', 'CAP101', 'Capstone Project 1', 3.00, 0.00, 3.00, 0.00, 'QUAMET');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_term`
--

CREATE TABLE `tbl_term` (
  `ID` int(11) NOT NULL,
  `TERM` varchar(30) NOT NULL,
  `DESCRIPTION` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_term`
--

INSERT INTO `tbl_term` (`ID`, `TERM`, `DESCRIPTION`) VALUES
(1, '1ST SEM', 'First Semester'),
(2, '2ND SEM', 'Second Semester'),
(3, 'SUMMER', 'Summer Class');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `ID` int(11) NOT NULL,
  `USERNAME` varchar(50) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `FULLNAME` varchar(150) NOT NULL,
  `EMAIL` varchar(150) NOT NULL,
  `USERTYPE_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`ID`, `USERNAME`, `PASSWORD`, `FULLNAME`, `EMAIL`, `USERTYPE_ID`) VALUES
(1, 'WendelCaayon', '$2y$10$2XR6czq4eWu/0i6bpWYHAeuvMbavs6BvQtYSy8h0g89D1XzIVOdz.', 'Wendel Ca-ayon', 'wndlcaayon@gmail.com', 5),
(2, 'admin', '$2y$10$xqgqUYo5dzaH86hJXCi1Kuybdakkst.ZQBvV9XFccEZlb4IdkqVTm', 'Administrator', 'admin@gmail.com', 1),
(3, 'admin2', '$2y$10$6UeABJOqEvQ2BFAeaTs/zOQZpjT7DtodJ4zy55b/7UuwANpimstAy', 'Naruto Uzumaki', 'naruto@gmail.com', 1),
(4, 'admin3', '$2y$10$zWzzRWWM63Mvf2ugt6FDSOD8bwvLjv37b5f7yGF1NuGFAnvGs4Asi', 'Roronoa Zoro', 'zoro@gmail.com', 1),
(5, 'registrar1', '$2y$10$.Qv9d7rSHP02YzmlwtWFWOKJ.n4.MgX5kTzPF8brCGFAQ9SLg5Qxa', 'Leni Robredo', 'leni@gmail.com', 2),
(6, 'registrar2', '$2y$10$lJ7swMVm6TARq0PEdPnUyubKcVTf3HZJtwcD/AZ/8OeUAsRDHZ2r6', 'Robin Padilla', 'robin@gmail.com', 2),
(7, 'registrar3', '$2y$10$WD/Ap03lF4qXzNSBfK50ceZbXUeQWRdvETHAvFydXAPCcAk6w/dqu', 'Sarah Duterte', 'sarah@gmail.com', 2),
(8, 'dean1', '$2y$10$FhSxRAGpevsxkdW0aeUQK.VxOPJcO4Kd9k9gyc3tXiSioMqEgZWpq', 'Juan luna', 'luna@gmail.com', 3),
(9, 'dean2', '$2y$10$2cCi4IQlu41sbI2QO1sCneCWGiltvRZT9RrjYKeLXUydGrpu4owve', 'Monkey D. Luffy', 'monkey@gmail.com', 3),
(10, 'dean3', '$2y$10$PRYP2co9hdQjKOECV0yt2e28fnwNW52cUWvdTqTh3Utw2.dZ2BQam', 'Minato Namikaze', 'minato@gmail.com', 3),
(11, 'instructor1', '$2y$10$XTCAEmSfO68TYXnOfzIH.eb62BLFj4dQ/WKYxQl8HCSpFfvntI9dy', 'Gregorio Del Pilar', 'goyo@gmail.com', 4),
(12, 'instructor2', '$2y$10$SYvr5vx.orMIlGrFrA9GQO/prYyZkvUFhreT3tHpQTjslbNRob/pu', 'Antonio Luna', 'Antonio@gmail.com', 4),
(13, 'instructor3', '$2y$10$tr1FatEQJ4AGPpFhRLHUc..XJr6PX9tLC3f9K2o4UGuY9vindwhwS', 'Emilio Aguinaldo', 'meong@gmail.com', 4),
(14, 'student2', '$2y$10$ivdO0y1OSITumreTHo9y.Och9I8dJO.zfjGOl2g4BDbY8p73XTtEu', 'Juan Dela Cruz', 'juan@gmail.com', 5),
(15, 'student3', '$2y$10$HK27SJAqm3K5xEPmFLOGauOWz0JDgre1HuWHfXh62hrjMxBbZqZgC', 'Pedro Penduko', 'Penduko@gmail.com', 5);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_usertype`
--

CREATE TABLE `tbl_usertype` (
  `ID` int(11) NOT NULL,
  `USERTYPE` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_usertype`
--

INSERT INTO `tbl_usertype` (`ID`, `USERTYPE`) VALUES
(1, 'Admin'),
(3, 'Dean'),
(4, 'Instructor'),
(2, 'Registrar'),
(5, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_yearlevel`
--

CREATE TABLE `tbl_yearlevel` (
  `ID` int(11) NOT NULL,
  `YEARLEVEL` varchar(20) NOT NULL,
  `DESCRIPTION` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_yearlevel`
--

INSERT INTO `tbl_yearlevel` (`ID`, `YEARLEVEL`, `DESCRIPTION`) VALUES
(1, '1ST', 'First Year'),
(2, '2ND', 'Second Year'),
(3, '3RD', 'Third Year'),
(4, '4TH', 'Fourth Year');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_course`
--
ALTER TABLE `tbl_course`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_instructors`
--
ALTER TABLE `tbl_instructors`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_schoolyear`
--
ALTER TABLE `tbl_schoolyear`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_section`
--
ALTER TABLE `tbl_section`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_subjects`
--
ALTER TABLE `tbl_subjects`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_term`
--
ALTER TABLE `tbl_term`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`),
  ADD KEY `USERTYPE_ID` (`USERTYPE_ID`);

--
-- Indexes for table `tbl_usertype`
--
ALTER TABLE `tbl_usertype`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `USERTYPE` (`USERTYPE`);

--
-- Indexes for table `tbl_yearlevel`
--
ALTER TABLE `tbl_yearlevel`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_course`
--
ALTER TABLE `tbl_course`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_instructors`
--
ALTER TABLE `tbl_instructors`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_schoolyear`
--
ALTER TABLE `tbl_schoolyear`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_section`
--
ALTER TABLE `tbl_section`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_subjects`
--
ALTER TABLE `tbl_subjects`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_term`
--
ALTER TABLE `tbl_term`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_usertype`
--
ALTER TABLE `tbl_usertype`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_yearlevel`
--
ALTER TABLE `tbl_yearlevel`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD CONSTRAINT `tbl_users_ibfk_1` FOREIGN KEY (`USERTYPE_ID`) REFERENCES `tbl_usertype` (`ID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
