-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 08, 2026 at 08:41 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u770916388_ege_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `ege_bookmarks`
--

CREATE TABLE `ege_bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_exam_blueprint_tasks`
--

CREATE TABLE `ege_exam_blueprint_tasks` (
  `id` int(11) NOT NULL,
  `exam_version_id` int(11) NOT NULL,
  `task_number` int(11) NOT NULL,
  `part_number` tinyint(4) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `content_area` varchar(255) DEFAULT NULL,
  `difficulty_level` enum('низкая','повышенная','высокая') NOT NULL DEFAULT 'низкая',
  `answer_format` enum('Краткий','Развернутый') NOT NULL DEFAULT 'Краткий',
  `max_score` tinyint(4) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_exam_sessions`
--

CREATE TABLE `ege_exam_sessions` (
  `id` int(11) NOT NULL,
  `exam_version_id` int(11) DEFAULT NULL,
  `session_year` int(11) NOT NULL,
  `session_type` enum('demo','march','april','may','june','reserve','early','main','training','teacher','other') NOT NULL DEFAULT 'training',
  `session_month` varchar(30) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `official_date` date DEFAULT NULL,
  `is_official` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_exam_versions`
--

CREATE TABLE `ege_exam_versions` (
  `id` int(11) NOT NULL,
  `exam_year` int(11) NOT NULL,
  `level` enum('profile','base') NOT NULL DEFAULT 'profile',
  `title` varchar(255) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_exam_versions`
--

INSERT INTO `ege_exam_versions` (`id`, `exam_year`, `level`, `title`, `is_current`, `notes`, `created_at`) VALUES
(1, 2026, 'profile', 'ЕГЭ 2026 профильная математика', 1, 'Текущая структура для подготовки на сайте.', '2026-05-08 08:21:49');

-- --------------------------------------------------------

--
-- Table structure for table `ege_home_blocks`
--

CREATE TABLE `ege_home_blocks` (
  `id` int(11) NOT NULL,
  `block_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body_html` text DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_home_blocks`
--

INSERT INTO `ege_home_blocks` (`id`, `block_key`, `title`, `body_html`, `button_text`, `button_url`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'theory', 'Теория по темам', '<p>Краткие объяснения, формулы и типовые методы решения.</p>', 'Открыть темы', '/topics.php', 1, 1, '2026-05-07 16:32:54'),
(2, 'tasks', 'Задания по номерам', '<p>Практика по каждому номеру ЕГЭ с подробными решениями.</p>', 'Выбрать номер', '/tasks.php', 2, 1, '2026-05-07 16:32:54'),
(3, 'variants', 'Пробные варианты', '<p>Варианты в формате экзамена для регулярной тренировки.</p>', 'Открыть варианты', '/variants.php', 3, 1, '2026-05-07 16:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `ege_pages`
--

CREATE TABLE `ege_pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `meta_description` text DEFAULT NULL,
  `h1` varchar(255) DEFAULT NULL,
  `intro_html` text DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_pages`
--

INSERT INTO `ege_pages` (`id`, `slug`, `title`, `meta_description`, `h1`, `intro_html`, `is_published`, `created_at`) VALUES
(1, 'home', 'Подготовка к ЕГЭ по математике — Maths4U', 'Подготовка к ЕГЭ по математике: теория, задания по номерам, практика, варианты и подробные решения.', 'Подготовка к ЕГЭ по математике', '<p>Теория, практика, задания по номерам и варианты для подготовки к ЕГЭ по математике.</p>', 1, '2026-05-07 16:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `ege_questions`
--

CREATE TABLE `ege_questions` (
  `id` int(11) NOT NULL,
  `task_type_id` int(11) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `subtopic_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body_html` longtext NOT NULL,
  `solution_html` longtext DEFAULT NULL,
  `marking_scheme_html` longtext DEFAULT NULL,
  `answer_text` varchar(255) DEFAULT NULL,
  `answer_type` enum('short','full') NOT NULL DEFAULT 'short',
  `max_score` tinyint(4) NOT NULL DEFAULT 1,
  `difficulty` enum('низкая','повышенная','высокая') NOT NULL DEFAULT 'низкая',
  `source` varchar(255) DEFAULT NULL,
  `source_name` varchar(255) DEFAULT NULL,
  `source_year` int(11) DEFAULT NULL,
  `source_month` varchar(50) DEFAULT NULL,
  `source_period` enum('demo','march','april','may','june','reserve','early','main','teacher','training','other') DEFAULT NULL,
  `source_variant_code` varchar(100) DEFAULT NULL,
  `source_task_number` int(11) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `source_external_id` varchar(100) DEFAULT NULL,
  `auto_check_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `checked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_questions`
--

INSERT INTO `ege_questions` (`id`, `task_type_id`, `topic_id`, `subtopic_id`, `title`, `body_html`, `solution_html`, `marking_scheme_html`, `answer_text`, `answer_type`, `max_score`, `difficulty`, `source`, `source_name`, `source_year`, `source_month`, `source_period`, `source_variant_code`, `source_task_number`, `source_url`, `source_external_id`, `auto_check_enabled`, `created_by`, `is_published`, `checked`, `created_at`, `updated_at`) VALUES
(1, 1, 3, NULL, 'Нахождение катета по синусу', '<p>В треугольнике \\(ABC\\) угол \\(C\\) равен \\(90^\\circ\\), \\(AC=25\\), \\(\\sin A=\\frac{12}{13}\\). Найдите \\(BC\\).</p>', '<p>Так как \\(\\angle C = 90^\\circ\\), то \\(AB\\) — гипотенуза.</p>\r\n\r\n<p>По условию:</p>\r\n\r\n\\[\r\n\\sin A = \\frac{12}{13}\r\n\\]\r\n\r\n<p>В прямоугольном треугольнике</p>\r\n\r\n\\[\r\n\\sin A = \\frac{BC}{AB}\r\n\\]\r\n\r\n<p>Значит, отношение сторон можно записать так:</p>\r\n\r\n\\[\r\nBC:AB = 12:13\r\n\\]\r\n\r\n<p>Тогда второй катет \\(AC\\) соответствует числу \\(5\\), так как</p>\r\n\r\n\\[\r\n5^2+12^2=13^2\r\n\\]\r\n\r\n<p>Следовательно,</p>\r\n\r\n\\[\r\nAC:BC:AB = 5:12:13\r\n\\]\r\n\r\n<p>Так как \\(AC=25\\), то</p>\r\n\r\n\\[\r\n5k=25\r\n\\]\r\n\r\n\\[\r\nk=5\r\n\\]\r\n\r\n<p>Тогда</p>\r\n\r\n\\[\r\nBC=12k=12\\cdot 5=60\r\n\\]\r\n\r\n<p><strong>Ответ:</strong> \\(60\\).</p>', NULL, '60', 'short', 1, 'низкая', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 0, '2026-05-08 06:33:38', '2026-05-08 07:03:42');

-- --------------------------------------------------------

--
-- Table structure for table `ege_question_attempts`
--

CREATE TABLE `ege_question_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `check_mode` enum('auto','self','teacher','ai') NOT NULL DEFAULT 'auto',
  `score` decimal(5,2) DEFAULT NULL,
  `max_score` tinyint(4) DEFAULT NULL,
  `self_marked` tinyint(1) NOT NULL DEFAULT 0,
  `feedback_html` longtext DEFAULT NULL,
  `time_spent_seconds` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_question_media`
--

CREATE TABLE `ege_question_media` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `role` enum('question','solution','hint','extra') NOT NULL DEFAULT 'question',
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_question_media`
--

INSERT INTO `ege_question_media` (`id`, `question_id`, `role`, `file_path`, `file_type`, `alt_text`, `sort_order`, `created_at`) VALUES
(1, 1, 'question', '/uploads/questions/task_1/task_1_20260508065255_df30d8af81.jpg', 'image/jpeg', '', 0, '2026-05-08 06:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `ege_question_options`
--

CREATE TABLE `ege_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `body_html` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_question_tags`
--

CREATE TABLE `ege_question_tags` (
  `question_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_tags`
--

CREATE TABLE `ege_tags` (
  `id` int(11) NOT NULL,
  `tag_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_task_subtopics`
--

CREATE TABLE `ege_task_subtopics` (
  `id` int(11) NOT NULL,
  `task_type_id` int(11) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `short_description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_task_subtopics`
--

INSERT INTO `ege_task_subtopics` (`id`, `task_type_id`, `topic_id`, `title`, `slug`, `short_description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Прямоугольный треугольник', 'right-triangle', NULL, 1, 1, '2026-05-08 08:27:39', '2026-05-08 08:37:00'),
(2, 1, 3, 'Равнобедренный треугольник', 'isosceles-triangle', NULL, 2, 1, '2026-05-08 08:27:39', '2026-05-08 08:37:00'),
(3, 1, 3, 'Произвольные треугольники', 'general-triangles', NULL, 3, 1, '2026-05-08 08:27:39', '2026-05-08 08:37:00'),
(4, 1, 3, 'Параллелограммы', 'parallelograms', NULL, 4, 1, '2026-05-08 08:27:39', '2026-05-08 08:27:39'),
(5, 1, 3, 'Трапеция', 'trapezoid', NULL, 5, 1, '2026-05-08 08:27:39', '2026-05-08 08:27:39'),
(6, 1, 3, 'Центральные и вписанные углы', 'central-and-inscribed-angles', NULL, 6, 1, '2026-05-08 08:27:39', '2026-05-08 08:27:39'),
(7, 1, 3, 'Касательная, хорда, секущая', 'tangent-chord-secant', NULL, 7, 1, '2026-05-08 08:27:39', '2026-05-08 08:27:39'),
(8, 1, 3, 'Вписанные окружности', 'inscribed-circles', NULL, 8, 1, '2026-05-08 08:27:39', '2026-05-08 08:27:39'),
(9, 1, 3, 'Описанные окружности', 'circumscribed-circles', NULL, 9, 1, '2026-05-08 08:27:39', '2026-05-08 08:27:39'),
(16, 1, 3, 'Трапеции', 'trapezoids', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(17, 1, 3, 'Хорды, касательные и секущие', 'chords-tangents-secants', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(18, 2, 7, 'Векторы и действия с ними', 'vector-operations', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(19, 2, 7, 'Координаты векторов', 'vector-coordinates', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(20, 2, 7, 'Длина вектора и скалярное произведение', 'vector-length-dot-product', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(21, 3, 3, 'Куб', 'cube', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(22, 3, 3, 'Прямоугольный параллелепипед', 'rectangular-box', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(23, 3, 3, 'Призмы', 'prisms', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(24, 3, 3, 'Пирамиды', 'pyramids', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(25, 3, 3, 'Составные многогранники', 'composite-polyhedra', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(26, 3, 3, 'Площадь поверхности', 'surface-area', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(27, 3, 3, 'Объёмы тел', 'volumes', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(28, 3, 3, 'Цилиндр', 'cylinder', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(29, 3, 3, 'Конус', 'cone', NULL, 9, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(30, 3, 3, 'Шар', 'sphere', NULL, 10, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(31, 3, 3, 'Комбинации тел', 'solid-combinations', NULL, 11, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(32, 4, 4, 'Классическое определение вероятности', 'classical-probability', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(33, 4, 4, 'Простые события', 'simple-events', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(34, 4, 4, 'Вероятность по таблицам и диаграммам', 'probability-from-tables', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(35, 5, 4, 'Сложение вероятностей', 'addition-rule', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(36, 5, 4, 'Умножение вероятностей', 'multiplication-rule', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(37, 5, 4, 'Независимые события', 'independent-events', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(38, 5, 4, 'Условная вероятность', 'conditional-probability', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(39, 5, 4, 'Комбинаторные модели', 'combinatorial-models', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(40, 6, 1, 'Линейные и квадратные уравнения', 'linear-quadratic-equations', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(41, 6, 1, 'Рациональные уравнения', 'rational-equations', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(42, 6, 1, 'Иррациональные уравнения', 'irrational-equations', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(43, 6, 1, 'Показательные уравнения', 'exponential-equations', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(44, 6, 1, 'Логарифмические уравнения', 'logarithmic-equations', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(45, 6, 1, 'Тригонометрические уравнения', 'trigonometric-equations', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(46, 7, 1, 'Рациональные числовые выражения', 'rational-numeric-expressions', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(47, 7, 1, 'Алгебраические выражения и дроби', 'algebraic-expressions-fractions', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(48, 7, 1, 'Степени и корни', 'powers-and-roots', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(49, 7, 1, 'Иррациональные выражения', 'irrational-expressions', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(50, 7, 1, 'Логарифмические выражения', 'logarithmic-expressions', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(51, 7, 1, 'Тригонометрические выражения', 'trigonometric-expressions', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(52, 7, 1, 'Формулы сокращённого умножения', 'special-products', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(53, 7, 1, 'Преобразование буквенных выражений', 'literal-expressions', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(54, 8, 2, 'Физический смысл производной', 'derivative-physical-meaning', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(55, 8, 2, 'Геометрический смысл производной', 'derivative-geometric-meaning', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(56, 8, 2, 'Производная по графику', 'derivative-from-graph', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(57, 8, 2, 'Исследование функции производной', 'function-analysis-with-derivative', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(58, 8, 2, 'Первообразная и площадь', 'antiderivative-and-area', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(59, 9, 8, 'Линейные модели', 'linear-models', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(60, 9, 8, 'Квадратичные и степенные модели', 'quadratic-power-models', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(61, 9, 8, 'Рациональные модели', 'rational-models', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(62, 9, 8, 'Иррациональные модели', 'irrational-models', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(63, 9, 8, 'Показательные модели', 'exponential-models', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(64, 9, 8, 'Логарифмические модели', 'logarithmic-models', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(65, 9, 8, 'Тригонометрические модели', 'trigonometric-models', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(66, 9, 8, 'Разные прикладные задачи', 'mixed-applied-problems', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(67, 10, 5, 'Проценты, смеси и сплавы', 'percent-mixtures-alloys', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(68, 10, 5, 'Движение по прямой', 'linear-motion', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(69, 10, 5, 'Движение по воде', 'motion-on-water', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(70, 10, 5, 'Движение по окружности', 'circular-motion', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(71, 10, 5, 'Совместная работа', 'work-rate-problems', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(72, 10, 5, 'Прогрессии', 'progressions', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(73, 10, 5, 'Разные текстовые задачи', 'mixed-word-problems', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(74, 11, 2, 'Линейные функции', 'linear-functions', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(75, 11, 2, 'Квадратичные функции', 'quadratic-functions', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(76, 11, 2, 'Обратная пропорциональность', 'reciprocal-functions', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(77, 11, 2, 'Корни и модули', 'roots-and-absolute-value', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(78, 11, 2, 'Показательные и логарифмические графики', 'exponential-logarithmic-graphs', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(79, 11, 2, 'Тригонометрические графики', 'trigonometric-graphs', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(80, 11, 2, 'Комбинированные графики', 'combined-graphs', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(81, 12, 2, 'Исследование без производной', 'analysis-without-derivative', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(82, 12, 2, 'Степенные и иррациональные функции', 'power-irrational-functions', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(83, 12, 2, 'Рациональные функции', 'rational-functions', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(84, 12, 2, 'Произведения и частные', 'products-and-quotients', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(85, 12, 2, 'Показательные и логарифмические функции', 'exponential-logarithmic-functions', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(86, 12, 2, 'Тригонометрические функции', 'trigonometric-functions', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(87, 13, 1, 'Показательные уравнения', 'full-exponential-equations', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(88, 13, 1, 'Рациональные уравнения', 'full-rational-equations', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(89, 13, 1, 'Иррациональные уравнения', 'full-irrational-equations', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(90, 13, 1, 'Логарифмические уравнения', 'full-logarithmic-equations', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(91, 13, 1, 'Тригонометрические уравнения: сведение к квадратному', 'trig-quadratic-substitution', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(92, 13, 1, 'Тригонометрические уравнения: однородные', 'trig-homogeneous', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(93, 13, 1, 'Тригонометрические уравнения: разложение на множители', 'trig-factorisation', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(94, 13, 1, 'Отбор корней на промежутке', 'root-selection-interval', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(95, 13, 1, 'ОДЗ и ограничения', 'domain-restrictions', NULL, 9, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(96, 13, 1, 'Смешанные уравнения', 'mixed-equations', NULL, 10, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(97, 14, 3, 'Расстояние от точки до прямой', 'distance-point-line', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(98, 14, 3, 'Расстояние от точки до плоскости', 'distance-point-plane', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(99, 14, 3, 'Расстояние между прямыми и плоскостями', 'distance-lines-planes', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(100, 14, 3, 'Угол между прямой и плоскостью', 'angle-line-plane', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(101, 14, 3, 'Угол между плоскостями', 'angle-between-planes', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(102, 14, 3, 'Угол между скрещивающимися прямыми', 'angle-skew-lines', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(103, 14, 3, 'Сечения призм', 'prism-sections', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(104, 14, 3, 'Сечения пирамид', 'pyramid-sections', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(105, 14, 3, 'Объёмы многогранников', 'polyhedra-volumes', NULL, 9, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(106, 14, 3, 'Круглые тела и комбинации', 'round-solids-combinations', NULL, 10, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(107, 15, 1, 'Рациональные неравенства', 'rational-inequalities', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(108, 15, 1, 'Неравенства с радикалами', 'radical-inequalities', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(109, 15, 1, 'Показательные неравенства', 'exponential-inequalities', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(110, 15, 1, 'Логарифмические неравенства', 'logarithmic-inequalities', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(111, 15, 1, 'Метод рационализации', 'rationalisation-method', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(112, 15, 1, 'Неравенства с переменным основанием', 'variable-base-inequalities', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(113, 15, 1, 'Неравенства с модулем', 'absolute-value-inequalities', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(114, 15, 1, 'Тригонометрические неравенства', 'trigonometric-inequalities', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(115, 15, 1, 'Смешанные неравенства', 'mixed-inequalities', NULL, 9, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(116, 15, 1, 'Системы неравенств', 'systems-of-inequalities', NULL, 10, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(117, 16, 9, 'Вклады', 'deposits', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(118, 16, 9, 'Кредиты с равными платежами', 'equal-payment-loans', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(119, 16, 9, 'Кредиты с изменяющимися платежами', 'variable-payment-loans', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(120, 16, 9, 'Оптимальный выбор', 'optimal-choice', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(121, 16, 9, 'Разные финансовые задачи', 'mixed-finance-problems', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(122, 17, 3, 'Треугольники', 'full-triangles', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(123, 17, 3, 'Четырёхугольники', 'full-quadrilaterals', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(124, 17, 3, 'Окружности', 'full-circles', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(125, 17, 3, 'Вписанные окружности', 'full-inscribed-circles', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(126, 17, 3, 'Описанные окружности', 'full-circumscribed-circles', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(127, 17, 3, 'Окружности и треугольники', 'circles-and-triangles', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(128, 17, 3, 'Окружности и четырёхугольники', 'circles-and-quadrilaterals', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(129, 17, 3, 'Площади и отношения', 'areas-and-ratios', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(130, 17, 3, 'Доказательство в планиметрии', 'planimetry-proof', NULL, 9, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(131, 18, 10, 'Уравнения с параметром', 'parameter-equations', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(132, 18, 10, 'Неравенства с параметром', 'parameter-inequalities', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(133, 18, 10, 'Системы с параметром', 'parameter-systems', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(134, 18, 10, 'Параметр и модуль', 'parameter-absolute-value', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(135, 18, 10, 'Параметр и радикалы', 'parameter-radicals', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(136, 18, 10, 'Расположение корней квадратного трёхчлена', 'quadratic-root-location', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(137, 18, 10, 'Симметрия', 'symmetry-method', NULL, 7, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(138, 18, 10, 'Монотонность и оценки', 'monotonicity-estimates', NULL, 8, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(139, 18, 10, 'Графический метод', 'graphical-method', NULL, 9, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(140, 18, 10, 'Координатная плоскость ((x; a))', 'coordinate-plane-x-a', NULL, 10, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(141, 18, 10, 'Окружности и расстояния', 'circles-and-distances', NULL, 11, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(142, 18, 10, 'Функции с параметром', 'functions-with-parameter', NULL, 12, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(143, 19, 11, 'Делимость и остатки', 'divisibility-remainders', NULL, 1, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(144, 19, 11, 'Целые числа', 'integers', NULL, 2, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(145, 19, 11, 'Последовательности и прогрессии', 'sequences-progressions', NULL, 3, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(146, 19, 11, 'Числовые наборы и конструкции', 'number-sets-constructions', NULL, 4, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(147, 19, 11, 'Логические задачи', 'logic-problems', NULL, 5, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00'),
(148, 19, 11, 'Доказательства и оценки', 'proofs-and-estimates', NULL, 6, 1, '2026-05-08 08:37:00', '2026-05-08 08:37:00');

-- --------------------------------------------------------

--
-- Table structure for table `ege_task_types`
--

CREATE TABLE `ege_task_types` (
  `id` int(11) NOT NULL,
  `task_number` int(11) NOT NULL,
  `part_number` tinyint(4) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `difficulty_level` varchar(30) NOT NULL DEFAULT 'низкая',
  `answer_format` varchar(30) NOT NULL DEFAULT 'Краткий',
  `max_score` tinyint(4) NOT NULL DEFAULT 1,
  `content_area` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_task_types`
--

INSERT INTO `ege_task_types` (`id`, `task_number`, `part_number`, `title`, `difficulty_level`, `answer_format`, `max_score`, `content_area`, `short_description`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 1, 1, 'Планиметрия', 'низкая', 'Краткий', 1, 'Планиметрия', 'Задачи по планиметрии с кратким ответом.', 1, 1, '2026-05-07 16:32:54'),
(2, 2, 1, 'Векторы', 'низкая', 'Краткий', 1, 'Векторы', 'Векторы, координаты, длины, скалярное произведение.', 2, 1, '2026-05-07 16:32:54'),
(3, 3, 1, 'Стереометрия', 'низкая', 'Краткий', 1, 'Стереометрия', 'Простые задачи по пространственным фигурам.', 3, 1, '2026-05-07 16:32:54'),
(4, 4, 1, 'Простая теория вероятности', 'повышенная', 'Краткий', 1, 'Простая теория вероятности', 'Классическая вероятность и простые события.', 4, 1, '2026-05-07 16:32:54'),
(5, 5, 1, 'Сложная вероятность', 'низкая', 'Краткий', 1, 'Сложная вероятность', 'Сложные события и вероятностные модели.', 5, 1, '2026-05-07 16:32:54'),
(6, 6, 1, 'Уравнения', 'низкая', 'Краткий', 1, 'Уравнения', 'Простейшие уравнения с кратким ответом.', 6, 1, '2026-05-07 16:32:54'),
(7, 7, 1, 'Вычисления и преобразования', 'низкая', 'Краткий', 1, 'Вычисления и преобразования', 'Преобразование выражений.', 7, 1, '2026-05-07 16:32:54'),
(8, 8, 1, 'Производная и первообразная', 'низкая', 'Краткий', 1, 'Производная и первообразная', 'Производная, первообразная и их применение.', 8, 1, '2026-05-07 16:32:54'),
(9, 9, 1, 'Прикладная задача', 'повышенная', 'Краткий', 1, 'Прикладная задача', 'Задачи с формулами и практическими моделями.', 9, 1, '2026-05-07 16:32:54'),
(10, 10, 1, 'Текстовая задача', 'повышенная', 'Краткий', 1, 'Текстовая задача', 'Движение, работа, смеси, проценты.', 10, 1, '2026-05-07 16:32:54'),
(11, 11, 1, 'Графики функций', 'повышенная', 'Краткий', 1, 'Графики функций', 'Анализ графиков функций.', 11, 1, '2026-05-07 16:32:54'),
(12, 12, 1, 'Анализ функций', 'повышенная', 'Краткий', 1, 'Анализ функций', 'Наибольшее и наименьшее значение функций.', 12, 1, '2026-05-07 16:32:54'),
(13, 13, 2, 'Уравнения', 'повышенная', 'Развернутый', 2, 'Уравнения', 'Уравнения с развёрнутым решением.', 13, 1, '2026-05-07 16:32:54'),
(14, 14, 2, 'Стереометрия', 'повышенная', 'Развернутый', 3, 'Стереометрия', 'Стереометрическая задача.', 14, 1, '2026-05-07 16:32:54'),
(15, 15, 2, 'Неравенства', 'повышенная', 'Развернутый', 2, 'Неравенства', 'Неравенства с развёрнутым решением.', 15, 1, '2026-05-07 16:32:54'),
(16, 16, 2, 'Экономическая задача', 'повышенная', 'Развернутый', 2, 'Экономическая задача', 'Финансовая математика.', 16, 1, '2026-05-07 16:32:54'),
(17, 17, 2, 'Планиметрия', 'повышенная', 'Развернутый', 3, 'Планиметрия', 'Планиметрическая задача.', 17, 1, '2026-05-07 16:32:54'),
(18, 18, 2, 'Задача с параметром', 'высокая', 'Развернутый', 4, 'Задача с параметром', 'Уравнения, неравенства и системы с параметром.', 18, 1, '2026-05-07 16:32:54'),
(19, 19, 2, 'Числа и их свойства', 'высокая', 'Развернутый', 4, 'Числа и их свойства', 'Числа, делимость, логика и доказательства.', 19, 1, '2026-05-07 16:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `ege_teacher_students`
--

CREATE TABLE `ege_teacher_students` (
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_topics`
--

CREATE TABLE `ege_topics` (
  `id` int(11) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `icon` varchar(20) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_topics`
--

INSERT INTO `ege_topics` (`id`, `slug`, `title`, `short_description`, `icon`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'algebra', 'Алгебра', 'Уравнения, неравенства и преобразования.', '∑', 4, 1, '2026-05-07 16:32:54'),
(2, 'functions', 'Функции', 'Графики, производная и анализ функций.', 'ƒ', 5, 1, '2026-05-07 16:32:54'),
(3, 'geometry', 'Геометрия', 'Планиметрия и стереометрия.', '△', 1, 1, '2026-05-07 16:32:54'),
(4, 'probability', 'Вероятность', 'Вероятность простых и сложных событий.', 'P', 3, 1, '2026-05-07 16:32:54'),
(5, 'word-problems', 'Текстовые задачи', 'Движение, работа, смеси и проценты.', '%', 6, 1, '2026-05-07 16:32:54'),
(6, 'second-part', 'Вторая часть', 'Задания 13–19 с подробным оформлением решений.', 'Ⅱ', 6, 1, '2026-05-07 16:32:54'),
(7, 'vectors', 'Векторы', 'Векторы, координаты и скалярное произведение.', '→', 2, 1, '2026-05-08 08:37:00'),
(8, 'applications', 'Прикладные задачи', 'Практические задачи с формулами.', '⚙', 7, 1, '2026-05-08 08:37:00'),
(9, 'finance', 'Финансовая математика', 'Кредиты, вклады и оптимальный выбор.', '₽', 8, 1, '2026-05-08 08:37:00'),
(10, 'parameters', 'Параметры', 'Задачи с параметром.', 'a', 9, 1, '2026-05-08 08:37:00'),
(11, 'number-theory', 'Числа и логика', 'Числа, делимость, конструкции и доказательства.', '#', 10, 1, '2026-05-08 08:37:00');

-- --------------------------------------------------------

--
-- Table structure for table `ege_users`
--

CREATE TABLE `ege_users` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'student',
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ege_users`
--

INSERT INTO `ege_users` (`id`, `email`, `password_hash`, `full_name`, `role`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Antere77@mail.ru', '$2y$12$7LBk3Ll8QVOS9BKuXDo36.KAFpse6Bc5HpwNAb6GuLUPZHuzoIVsy', 'Albert77', 'admin', 'active', '2026-05-08 06:19:56', '2026-05-07 17:29:02', '2026-05-08 06:19:56');

-- --------------------------------------------------------

--
-- Table structure for table `ege_variants`
--

CREATE TABLE `ege_variants` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `variant_type` enum('official','training','teacher','random','diagnostic') NOT NULL DEFAULT 'training',
  `source_name` varchar(255) DEFAULT NULL,
  `source_year` int(11) DEFAULT NULL,
  `source_month` varchar(50) DEFAULT NULL,
  `source_period` enum('demo','march','april','may','june','reserve','early','main','teacher','random','training','other') NOT NULL DEFAULT 'training',
  `source_variant_code` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `is_official` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ege_variant_questions`
--

CREATE TABLE `ege_variant_questions` (
  `id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `task_number` int(11) NOT NULL,
  `position_in_variant` int(11) NOT NULL DEFAULT 0,
  `max_score` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ege_bookmarks`
--
ALTER TABLE `ege_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_question` (`user_id`,`question_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `ege_exam_blueprint_tasks`
--
ALTER TABLE `ege_exam_blueprint_tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blueprint_task` (`exam_version_id`,`task_number`),
  ADD KEY `idx_exam_version` (`exam_version_id`),
  ADD KEY `idx_task_number` (`task_number`),
  ADD KEY `idx_part_number` (`part_number`);

--
-- Indexes for table `ege_exam_sessions`
--
ALTER TABLE `ege_exam_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_session_slug` (`slug`),
  ADD KEY `exam_version_id` (`exam_version_id`),
  ADD KEY `idx_session_year` (`session_year`),
  ADD KEY `idx_session_type` (`session_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `ege_exam_versions`
--
ALTER TABLE `ege_exam_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_exam_year_level` (`exam_year`,`level`),
  ADD KEY `idx_current` (`is_current`);

--
-- Indexes for table `ege_home_blocks`
--
ALTER TABLE `ege_home_blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `block_key` (`block_key`);

--
-- Indexes for table `ege_pages`
--
ALTER TABLE `ege_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `ege_questions`
--
ALTER TABLE `ege_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_type` (`task_type_id`),
  ADD KEY `idx_topic` (`topic_id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_published` (`is_published`),
  ADD KEY `idx_checked` (`checked`),
  ADD KEY `idx_difficulty` (`difficulty`),
  ADD KEY `idx_subtopic_id` (`subtopic_id`),
  ADD KEY `idx_source_year` (`source_year`),
  ADD KEY `idx_source_period` (`source_period`),
  ADD KEY `idx_source_task_number` (`source_task_number`),
  ADD KEY `idx_answer_type` (`answer_type`);

--
-- Indexes for table `ege_question_attempts`
--
ALTER TABLE `ege_question_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_question_id` (`question_id`),
  ADD KEY `idx_user_question` (`user_id`,`question_id`),
  ADD KEY `idx_correct` (`is_correct`);

--
-- Indexes for table `ege_question_media`
--
ALTER TABLE `ege_question_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question_id` (`question_id`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `ege_question_options`
--
ALTER TABLE `ege_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question_id` (`question_id`),
  ADD KEY `idx_correct` (`is_correct`);

--
-- Indexes for table `ege_question_tags`
--
ALTER TABLE `ege_question_tags`
  ADD PRIMARY KEY (`question_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `ege_tags`
--
ALTER TABLE `ege_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag_key` (`tag_key`);

--
-- Indexes for table `ege_task_subtopics`
--
ALTER TABLE `ege_task_subtopics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_task_subtopic_slug` (`task_type_id`,`slug`),
  ADD KEY `idx_task_type_id` (`task_type_id`),
  ADD KEY `idx_topic_id` (`topic_id`);

--
-- Indexes for table `ege_task_types`
--
ALTER TABLE `ege_task_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_number` (`task_number`),
  ADD UNIQUE KEY `uq_task_number` (`task_number`);

--
-- Indexes for table `ege_teacher_students`
--
ALTER TABLE `ege_teacher_students`
  ADD PRIMARY KEY (`teacher_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `ege_topics`
--
ALTER TABLE `ege_topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `ege_users`
--
ALTER TABLE `ege_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `ege_variants`
--
ALTER TABLE `ege_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_variant_slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_variant_type` (`variant_type`),
  ADD KEY `idx_source_year` (`source_year`),
  ADD KEY `idx_source_period` (`source_period`),
  ADD KEY `idx_published` (`is_published`);

--
-- Indexes for table `ege_variant_questions`
--
ALTER TABLE `ege_variant_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_variant_question` (`variant_id`,`question_id`),
  ADD KEY `idx_variant_id` (`variant_id`),
  ADD KEY `idx_question_id` (`question_id`),
  ADD KEY `idx_task_number` (`task_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ege_bookmarks`
--
ALTER TABLE `ege_bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ege_exam_blueprint_tasks`
--
ALTER TABLE `ege_exam_blueprint_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ege_exam_sessions`
--
ALTER TABLE `ege_exam_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ege_exam_versions`
--
ALTER TABLE `ege_exam_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ege_home_blocks`
--
ALTER TABLE `ege_home_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ege_pages`
--
ALTER TABLE `ege_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ege_questions`
--
ALTER TABLE `ege_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ege_question_attempts`
--
ALTER TABLE `ege_question_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ege_question_media`
--
ALTER TABLE `ege_question_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ege_question_options`
--
ALTER TABLE `ege_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ege_tags`
--
ALTER TABLE `ege_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ege_task_subtopics`
--
ALTER TABLE `ege_task_subtopics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=271;

--
-- AUTO_INCREMENT for table `ege_task_types`
--
ALTER TABLE `ege_task_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `ege_topics`
--
ALTER TABLE `ege_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ege_users`
--
ALTER TABLE `ege_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ege_variants`
--
ALTER TABLE `ege_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ege_variant_questions`
--
ALTER TABLE `ege_variant_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ege_bookmarks`
--
ALTER TABLE `ege_bookmarks`
  ADD CONSTRAINT `ege_bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ege_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ege_bookmarks_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `ege_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ege_exam_blueprint_tasks`
--
ALTER TABLE `ege_exam_blueprint_tasks`
  ADD CONSTRAINT `ege_exam_blueprint_tasks_ibfk_1` FOREIGN KEY (`exam_version_id`) REFERENCES `ege_exam_versions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ege_exam_sessions`
--
ALTER TABLE `ege_exam_sessions`
  ADD CONSTRAINT `ege_exam_sessions_ibfk_1` FOREIGN KEY (`exam_version_id`) REFERENCES `ege_exam_versions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ege_questions`
--
ALTER TABLE `ege_questions`
  ADD CONSTRAINT `ege_questions_ibfk_1` FOREIGN KEY (`task_type_id`) REFERENCES `ege_task_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ege_questions_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `ege_topics` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ege_questions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `ege_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ege_questions_subtopic` FOREIGN KEY (`subtopic_id`) REFERENCES `ege_task_subtopics` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ege_question_attempts`
--
ALTER TABLE `ege_question_attempts`
  ADD CONSTRAINT `ege_question_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ege_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ege_question_attempts_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `ege_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ege_question_media`
--
ALTER TABLE `ege_question_media`
  ADD CONSTRAINT `ege_question_media_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `ege_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ege_question_options`
--
ALTER TABLE `ege_question_options`
  ADD CONSTRAINT `ege_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `ege_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ege_question_tags`
--
ALTER TABLE `ege_question_tags`
  ADD CONSTRAINT `ege_question_tags_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `ege_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ege_question_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `ege_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ege_task_subtopics`
--
ALTER TABLE `ege_task_subtopics`
  ADD CONSTRAINT `ege_task_subtopics_ibfk_1` FOREIGN KEY (`task_type_id`) REFERENCES `ege_task_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ege_task_subtopics_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `ege_topics` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ege_teacher_students`
--
ALTER TABLE `ege_teacher_students`
  ADD CONSTRAINT `ege_teacher_students_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `ege_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ege_teacher_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `ege_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ege_variants`
--
ALTER TABLE `ege_variants`
  ADD CONSTRAINT `ege_variants_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `ege_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ege_variant_questions`
--
ALTER TABLE `ege_variant_questions`
  ADD CONSTRAINT `ege_variant_questions_ibfk_1` FOREIGN KEY (`variant_id`) REFERENCES `ege_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ege_variant_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `ege_questions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
