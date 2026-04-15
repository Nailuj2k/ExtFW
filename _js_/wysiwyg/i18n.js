// Definición de todos los idiomas y traducciones
const langs = {
  'en': {
    'access_denied': 'Access denied',
    'welcome': 'Welcome',
    'logout': 'Logout'
  },
  'es': {
    'access_denied': 'Acceso denegado',
    'welcome': 'Bienvenido',
    'logout': 'Cerrar sesión'
  },
  'fr': {
    'access_denied': 'Accès refusé',
    'welcome': 'Bienvenue',
    'logout': 'Déconnexion'
  }
};

// Variable global para el idioma actual
var lang = 'en';

// Función simple de traducción
function t(str) {
  if (langs[lang] && langs[lang][str] !== undefined) {
    return langs[lang][str];
  }
  return str; // Devuelve la clave original si no encuentra traducción
}

// Ejemplo de uso
console.log(t('access_denied')); // Muestra: Access denied

// Cambiar el idioma
lang = 'es';
console.log(t('access_denied')); // Muestra: Acceso denegado

// Cambiar a otro idioma
lang = 'fr';
console.log(t('welcome')); // Muestra: Bienvenue