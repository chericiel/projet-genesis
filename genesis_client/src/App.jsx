import './App.css'

import { Routes, Route } from 'react-router-dom';
import Connexion from '../pages/auth/Connexion';
import Inscription from '../pages/auth/Inscription';
import Verification from '../pages/auth/Verification';

export default function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<Connexion />} />
      <Route path="/register/:role" element={<Inscription />} />
      <Route path="/verify" element={<Verification />} />
    </Routes>
  );
}


