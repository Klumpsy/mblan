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
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
                display: ["Montserrat", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: "#FFF7ED",
                    100: "#FFEDD5",
                    200: "#FED7AA",
                    300: "#FDBA74",
                    400: "#FB923C",
                    500: "#F97316",
                    600: "#EA580C",
                    700: "#C2410C",
                    800: "#9A3412",
                    900: "#7C2D12",
                    950: "#431407",
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
