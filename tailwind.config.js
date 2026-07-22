import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Montserrat", ...defaultTheme.fontFamily.sans],
                display: ["'Chakra Petch'", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Driven by CSS variables emitted per active edition (see <x-theme-vars>).
                primary: {
                    50: "rgb(var(--c-primary-50) / <alpha-value>)",
                    100: "rgb(var(--c-primary-100) / <alpha-value>)",
                    200: "rgb(var(--c-primary-200) / <alpha-value>)",
                    300: "rgb(var(--c-primary-300) / <alpha-value>)",
                    400: "rgb(var(--c-primary-400) / <alpha-value>)",
                    500: "rgb(var(--c-primary-500) / <alpha-value>)",
                    600: "rgb(var(--c-primary-600) / <alpha-value>)",
                    700: "rgb(var(--c-primary-700) / <alpha-value>)",
                    800: "rgb(var(--c-primary-800) / <alpha-value>)",
                    900: "rgb(var(--c-primary-900) / <alpha-value>)",
                    950: "rgb(var(--c-primary-950) / <alpha-value>)",
                },
                // Fixed brand tokens for the dark "forge in a barn" aesthetic.
                forge: {
                    black: "#050807",
                    forest: "#0E1A16",
                    graphite: "#1A1A1A",
                    panel: "#111d18",
                    steel: "#AEB5B3",
                    mint: "#C7FFE0",
                },
                secondary: {
                    50: "#EFF6FF",
                    100: "#DBEAFE",
                    200: "#BFDBFE",
                    300: "#93C5FD",
                    400: "#60A5FA",
                    500: "#3B82F6",
                    600: "#2563EB",
                    700: "#1D4ED8",
                    800: "#1E40AF",
                    900: "#1E3A8A",
                    950: "#172554",
                },
                dark: {
                    50: "#F9FAFB",
                    100: "#F3F4F6",
                    200: "#E5E7EB",
                    300: "#D1D5DB",
                    400: "#9CA3AF",
                    500: "#6B7280",
                    600: "#4B5563",
                    700: "#374151",
                    800: "#1F2937",
                    900: "#111827",
                    950: "#030712",
                },
                success: {
                    50: "#F0FDF4",
                    100: "#DCFCE7",
                    200: "#BBF7D0",
                    300: "#86EFAC",
                    400: "#4ADE80",
                    500: "#22C55E",
                    600: "#16A34A",
                    700: "#15803D",
                    800: "#166534",
                    900: "#14532D",
                    950: "#052E16",
                },
                warning: {
                    50: "#FFFBEB",
                    100: "#FEF3C7",
                    200: "#FDE68A",
                    300: "#FCD34D",
                    400: "#FBBF24",
                    500: "#F59E0B",
                    600: "#D97706",
                    700: "#B45309",
                    800: "#92400E",
                    900: "#78350F",
                    950: "#451A03",
                },
                danger: {
                    50: "#FEF2F2",
                    100: "#FEE2E2",
                    200: "#FECACA",
                    300: "#FCA5A5",
                    400: "#F87171",
                    500: "#EF4444",
                    600: "#DC2626",
                    700: "#B91C1C",
                    800: "#991B1B",
                    900: "#7F1D1D",
                    950: "#450A0A",
                },
            },
            boxShadow: {
                soft: "0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)",
                button: "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
                card: "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                glow: "0 0 20px -2px rgb(var(--c-primary-500) / 0.55), 0 0 60px -20px rgb(var(--c-primary-400) / 0.4)",
                "glow-sm": "0 0 12px -2px rgb(var(--c-primary-500) / 0.5)",
            },
            keyframes: {
                "glow-pulse": {
                    "0%, 100%": { opacity: "0.55", filter: "brightness(1)" },
                    "50%": { opacity: "1", filter: "brightness(1.25)" },
                },
                shine: {
                    "0%": { transform: "translateX(-120%) skewX(-20deg)" },
                    "60%, 100%": { transform: "translateX(220%) skewX(-20deg)" },
                },
                float: {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-8px)" },
                },
                "seam-slide": {
                    "0%": { backgroundPosition: "0% 50%" },
                    "100%": { backgroundPosition: "200% 50%" },
                },
                "reveal-up": {
                    "0%": { opacity: "0", transform: "translateY(28px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
                twinkle: {
                    "0%, 100%": { opacity: "0.5" },
                    "50%": { opacity: "1" },
                },
                drift: {
                    "0%": { backgroundPosition: "0% 0%" },
                    "100%": { backgroundPosition: "100% 100%" },
                },
                "float-orb": {
                    "0%, 100%": { transform: "translateY(0) translateX(0)" },
                    "33%": { transform: "translateY(-18px) translateX(10px)" },
                    "66%": { transform: "translateY(10px) translateX(-8px)" },
                },
            },
            animation: {
                "glow-pulse": "glow-pulse 3.2s ease-in-out infinite",
                shine: "shine 4.5s ease-in-out infinite",
                float: "float 6s ease-in-out infinite",
                "seam-slide": "seam-slide 6s linear infinite",
            },
            borderRadius: {
                xl: "1rem",
                "2xl": "1.5rem",
                "3xl": "2rem",
            },
            spacing: {
                72: "18rem",
                84: "21rem",
                96: "24rem",
                128: "32rem",
            },
            maxWidth: {
                "8xl": "90rem",
                "9xl": "100rem",
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        color: theme("colors.dark.700"),
                        a: {
                            color: theme("colors.primary.600"),
                            "&:hover": {
                                color: theme("colors.primary.500"),
                            },
                        },
                        h1: {
                            color: theme("colors.dark.900"),
                            fontWeight: "700",
                        },
                        h2: {
                            color: theme("colors.dark.900"),
                            fontWeight: "600",
                        },
                        h3: {
                            color: theme("colors.dark.900"),
                            fontWeight: "600",
                        },
                        h4: {
                            color: theme("colors.dark.900"),
                            fontWeight: "600",
                        },
                    },
                },
                invert: {
                    css: {
                        color: theme("colors.dark.300"),
                        a: {
                            color: theme("colors.primary.400"),
                            "&:hover": {
                                color: theme("colors.primary.300"),
                            },
                        },
                        h1: {
                            color: theme("colors.dark.100"),
                        },
                        h2: {
                            color: theme("colors.dark.100"),
                        },
                        h3: {
                            color: theme("colors.dark.100"),
                        },
                        h4: {
                            color: theme("colors.dark.100"),
                        },
                    },
                },
            }),
        },
    },

    plugins: [forms, typography],

    darkMode: "class",
};
