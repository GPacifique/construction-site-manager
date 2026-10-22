import { Head, Link } from '@inertiajs/react';
import { useEffect, useId, useRef, useState } from 'react';

export default function Welcome({ auth, canLogin, canRegister, featuredProjects }) {
    const carouselRef = useRef(null);
    const [installPrompt, setInstallPrompt] = useState(null);
    const [installHelpOpen, setInstallHelpOpen] = useState(false);
    const [installed, setInstalled] = useState(false);
    const [isAppleMobile, setIsAppleMobile] = useState(false);

    useEffect(() => {
        const userAgent = window.navigator.userAgent;
        setIsAppleMobile(
            /iPad|iPhone|iPod/.test(userAgent) ||
                (navigator.platform === 'MacIntel' &&
                    navigator.maxTouchPoints > 1),
        );

        const handleInstallPrompt = (event) => {
            event.preventDefault();
            setInstallPrompt(event);
        };
        const handleInstalled = () => {
            setInstalled(true);
            setInstallPrompt(null);
            setInstallHelpOpen(false);
        };

        window.addEventListener('beforeinstallprompt', handleInstallPrompt);
        window.addEventListener('appinstalled', handleInstalled);
        setInstalled(
            window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true,
        );

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker
                .register('/service-worker.js')
                .catch((error) => {
                    console.error('Sitex service worker registration failed.', error);
                });
        }

        return () => {
            window.removeEventListener(
                'beforeinstallprompt',
                handleInstallPrompt,
            );
            window.removeEventListener('appinstalled', handleInstalled);
        };
    }, []);

    async function installApp() {
        if (!installPrompt) {
            setInstallHelpOpen(true);
            return;
        }

        await installPrompt.prompt();
        const choice = await installPrompt.userChoice;
        setInstallPrompt(null);

        if (choice.outcome === 'accepted') {
            setInstalled(true);
        }
    }

    function scrollProjects(direction) {
        carouselRef.current?.scrollBy({
            left: direction * Math.max(carouselRef.current.clientWidth * 0.85, 300),
            behavior: 'smooth',
        });
    }

    return (
        <>
            <Head title="Construction, under control">
                <meta
                    name="description"
                    content="Manage construction projects, teams, materials, budgets, and site progress in one place with Sitex."
                />
                <meta name="theme-color" content="#102b27" />
            </Head>

            <div className="min-h-screen overflow-hidden bg-[#f7f8f4] text-[#172522]">
                <header className="relative z-20 border-b border-white/10 bg-[#102b27] text-white">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
                        <a
                            href="/"
                            className="flex items-center gap-3"
                            aria-label="Sitex home"
                        >
                            <BrandMark />
                            <span className="text-xl font-bold tracking-tight">
                                sitex
                            </span>
                        </a>

                        <nav className="hidden items-center gap-8 text-sm font-medium text-white/75 md:flex">
                            <a className="transition hover:text-white" href="#platform">
                                Platform
                            </a>
                            <a className="transition hover:text-white" href="#projects">
                                Projects
                            </a>
                            <a className="transition hover:text-white" href="#teams">
                                For your team
                            </a>
                        </nav>

                        <div className="flex items-center gap-2 sm:gap-3">
                            {canLogin && !auth?.user && (
                                <Link
                                    href={route('login')}
                                    className="hidden rounded-lg px-3 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-white sm:inline-flex"
                                >
                                    Sign in
                                </Link>
                            )}
                            <button
                                type="button"
                                onClick={installApp}
                                disabled={installed}
                                className="inline-flex items-center gap-2 rounded-lg border border-[#d4ee9b]/50 bg-[#d4ee9b] px-3 py-2 text-sm font-bold text-[#173327] shadow-sm transition hover:bg-[#e2f6b8] disabled:cursor-default disabled:opacity-80 sm:px-4"
                            >
                                <DownloadIcon />
                                <span>
                                    {installed ? 'App installed' : 'Install app'}
                                </span>
                            </button>
                        </div>
                    </div>
                </header>

                <main>
                    <section className="relative isolate overflow-hidden bg-[#102b27] text-white">
                        <div className="absolute inset-0 -z-10 overflow-hidden">
                            <div className="absolute -right-28 -top-32 h-[32rem] w-[32rem] rounded-full bg-[#41644c]/30 blur-3xl" />
                            <div className="absolute -bottom-64 left-0 h-[32rem] w-[32rem] rounded-full bg-[#d5a143]/10 blur-3xl" />
                            <div className="absolute inset-0 opacity-[0.08] [background-image:linear-gradient(rgba(255,255,255,.3)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.3)_1px,transparent_1px)] [background-size:52px_52px]" />
                        </div>

                        <div className="mx-auto grid max-w-7xl items-center gap-6 px-5 pb-14 pt-12 sm:px-8 sm:pb-20 sm:pt-16 lg:grid-cols-[.9fr_1.1fr] lg:gap-4 lg:py-20">
                            <div className="relative z-10 max-w-2xl">
                                <div className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-[#d4ee9b]">
                                    <span className="h-2 w-2 rounded-full bg-[#d4ee9b]" />
                                    One clear view of every site
                                </div>
                                <h1 className="mt-6 text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                                    Build with
                                    <span className="block text-[#d4ee9b]">
                                        confidence.
                                    </span>
                                </h1>
                                <p className="mt-6 max-w-xl text-base leading-7 text-white/70 sm:text-lg sm:leading-8">
                                    Keep projects, people, materials, and costs
                                    moving together—from the office to the
                                    construction site.
                                </p>

                                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                    {canRegister && !auth?.user ? (
                                        <Link
                                            href={route('register')}
                                            className="inline-flex items-center justify-center gap-2 rounded-xl bg-[#d4ee9b] px-5 py-3.5 text-sm font-bold text-[#173327] shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-[#e2f6b8]"
                                        >
                                            Start managing projects
                                            <ArrowIcon />
                                        </Link>
                                    ) : (
                                        <Link
                                            href={route('dashboard')}
                                            className="inline-flex items-center justify-center gap-2 rounded-xl bg-[#d4ee9b] px-5 py-3.5 text-sm font-bold text-[#173327] shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-[#e2f6b8]"
                                        >
                                            Go to your workspace
                                            <ArrowIcon />
                                        </Link>
                                    )}
                                    <a
                                        href="#projects"
                                        className="inline-flex items-center justify-center rounded-xl border border-white/20 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10"
                                    >
                                        Explore featured sites
                                    </a>
                                </div>

                                <div className="mt-9 flex flex-wrap gap-x-6 gap-y-3 text-sm text-white/65">
                                    <span className="inline-flex items-center gap-2">
                                        <CheckIcon /> Built for the field
                                    </span>
                                    <span className="inline-flex items-center gap-2">
                                        <CheckIcon /> Ready on mobile
                                    </span>
                                    <span className="inline-flex items-center gap-2">
                                        <CheckIcon /> Your numbers, connected
                                    </span>
                                </div>
                            </div>

                            <div className="relative mx-auto w-full max-w-2xl lg:-mr-12">
                                <SiteIllustration className="w-full drop-shadow-[0_30px_60px_rgba(0,0,0,.22)]" />
                                <div className="absolute -bottom-2 left-2 rounded-2xl border border-white/60 bg-white/95 p-4 text-[#172522] shadow-xl sm:bottom-4 sm:left-4 sm:p-5">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-[#64716b]">
                                        Site operations
                                    </p>
                                    <p className="mt-1 text-lg font-bold">
                                        All in one place
                                    </p>
                                    <div className="mt-3 flex items-center gap-1.5">
                                        <span className="h-1.5 w-8 rounded-full bg-[#5d855d]" />
                                        <span className="h-1.5 w-8 rounded-full bg-[#d4ee9b]" />
                                        <span className="h-1.5 w-8 rounded-full bg-[#f1d99d]" />
                                    </div>
                                </div>
                                <div className="absolute right-2 top-5 rounded-xl border border-white/20 bg-[#1c3933]/90 px-3 py-2 text-xs font-semibold text-white shadow-lg backdrop-blur sm:right-0 sm:top-10 sm:px-4 sm:py-3 sm:text-sm">
                                    <span className="mr-2 inline-block h-2 w-2 rounded-full bg-[#d4ee9b]" />
                                    Projects in motion
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        id="platform"
                        className="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20"
                    >
                        <div className="grid gap-10 lg:grid-cols-[.8fr_1.2fr] lg:items-end">
                            <div>
                                <p className="text-sm font-bold uppercase tracking-[0.16em] text-[#62805a]">
                                    The whole picture
                                </p>
                                <h2 className="mt-3 max-w-lg text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">
                                    Site details that connect to the big plan.
                                </h2>
                            </div>
                            <p className="max-w-2xl text-base leading-7 text-[#64716b] lg:justify-self-end">
                                Sitex gives construction teams a shared place to
                                coordinate work and follow the details that
                                affect delivery. Less chasing updates. More
                                clarity on what happens next.
                            </p>
                        </div>

                        <div className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <FeatureCard
                                number="01"
                                title="Projects & teams"
                                text="Give each site a clear owner, plan, timeline, and team."
                                icon={<BlueprintIcon />}
                            />
                            <FeatureCard
                                number="02"
                                title="Materials & stores"
                                text="Follow requests, receipts, issues, returns, and stock."
                                icon={<MaterialsIcon />}
                            />
                            <FeatureCard
                                number="03"
                                title="Budgets & costs"
                                text="Bring estimates, commitments, costs, and payments into view."
                                icon={<BudgetIcon />}
                            />
                            <FeatureCard
                                number="04"
                                title="Field updates"
                                text="Keep progress and site records close to the people doing the work."
                                icon={<FieldIcon />}
                            />
                        </div>
                    </section>

                    <section
                        id="projects"
                        className="border-y border-[#e6e9e1] bg-white py-16 sm:py-20"
                    >
                        <div className="mx-auto max-w-7xl px-5 sm:px-8">
                            <div className="flex flex-wrap items-end justify-between gap-5">
                                <div>
                                    <p className="text-sm font-bold uppercase tracking-[0.16em] text-[#62805a]">
                                        Work in progress
                                    </p>
                                    <h2 className="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                        Projects on the ground
                                    </h2>
                                    <p className="mt-3 max-w-xl text-sm leading-6 text-[#64716b] sm:text-base">
                                        A look at projects whose owners have
                                        chosen to share them publicly.
                                    </p>
                                </div>

                                {featuredProjects?.length > 0 && (
                                    <div className="flex gap-2">
                                        <button
                                            type="button"
                                            onClick={() => scrollProjects(-1)}
                                            aria-label="Scroll to previous projects"
                                            className="grid h-11 w-11 place-items-center rounded-full border border-[#dfe4db] text-[#31433c] transition hover:border-[#31433c] hover:bg-[#f7f8f4]"
                                        >
                                            <ArrowIcon direction="left" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => scrollProjects(1)}
                                            aria-label="Scroll to more projects"
                                            className="grid h-11 w-11 place-items-center rounded-full border border-[#dfe4db] text-[#31433c] transition hover:border-[#31433c] hover:bg-[#f7f8f4]"
                                        >
                                            <ArrowIcon />
                                        </button>
                                    </div>
                                )}
                            </div>

                            {featuredProjects?.length ? (
                                <div
                                    ref={carouselRef}
                                    className="mt-9 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-4 [scrollbar-width:thin]"
                                    aria-label="Publicly featured projects"
                                >
                                    {featuredProjects.map((project, index) => (
                                        <article
                                            key={project.id}
                                            className="w-[min(84vw,360px)] shrink-0 snap-start overflow-hidden rounded-2xl border border-[#e6e9e1] bg-[#fcfcfa] shadow-sm"
                                        >
                                            <div className="relative h-48 overflow-hidden bg-[#e9eee3]">
                                                <SiteIllustration
                                                    className="absolute inset-0 h-full w-full"
                                                    compact
                                                    variant={index % 3}
                                                />
                                                <span className="absolute left-4 top-4 rounded-full border border-white/50 bg-white/90 px-3 py-1.5 text-xs font-bold capitalize text-[#31433c] shadow-sm">
                                                    {project.status.replaceAll(
                                                        '_',
                                                        ' ',
                                                    )}
                                                </span>
                                            </div>
                                            <div className="p-5">
                                                <p className="text-xs font-bold uppercase tracking-[0.12em] text-[#78857d]">
                                                    {project.construction_type ||
                                                        'Construction project'}
                                                </p>
                                                <h3 className="mt-2 text-lg font-semibold text-[#172522]">
                                                    {project.name}
                                                </h3>
                                                <p className="mt-1 text-sm text-[#64716b]">
                                                    {project.location ||
                                                        'Location not specified'}
                                                </p>
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            ) : (
                                <div className="mt-9 grid gap-6 overflow-hidden rounded-2xl border border-[#e6e9e1] bg-[#f7f8f4] p-6 sm:grid-cols-[1fr_.65fr] sm:items-center sm:p-9">
                                    <div>
                                        <span className="inline-flex rounded-full bg-[#e7eddc] px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#536d4f]">
                                            Shared by project owners
                                        </span>
                                        <h3 className="mt-4 text-xl font-semibold text-[#172522]">
                                            Featured project sites will appear here.
                                        </h3>
                                        <p className="mt-2 max-w-xl text-sm leading-6 text-[#64716b]">
                                            Projects are only shown after a
                                            company owner chooses to feature
                                            them. Private details stay private.
                                        </p>
                                        {canRegister && !auth?.user && (
                                            <Link
                                                href={route('register')}
                                                className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#36573e] hover:text-[#172522]"
                                            >
                                                Set up your company
                                                <ArrowIcon />
                                            </Link>
                                        )}
                                    </div>
                                    <div className="mx-auto w-full max-w-sm">
                                        <SiteIllustration className="w-full opacity-90" compact />
                                    </div>
                                </div>
                            )}
                        </div>
                    </section>

                    <section
                        id="teams"
                        className="mx-auto grid max-w-7xl gap-8 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[1fr_auto] lg:items-center"
                    >
                        <div>
                            <p className="text-sm font-bold uppercase tracking-[0.16em] text-[#62805a]">
                                From first pour to final handover
                            </p>
                            <h2 className="mt-3 max-w-2xl text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">
                                Give every person on site a clearer next step.
                            </h2>
                            <p className="mt-4 max-w-2xl text-base leading-7 text-[#64716b]">
                                Made for the people coordinating projects,
                                engineering the work, managing stores, and
                                keeping the numbers straight.
                            </p>
                        </div>
                        <div className="flex flex-col gap-3 sm:flex-row lg:flex-col">
                            {canRegister && !auth?.user && (
                                <Link
                                    href={route('register')}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-[#183b32] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-[#245444]"
                                >
                                    Create your workspace
                                    <ArrowIcon />
                                </Link>
                            )}
                            <button
                                type="button"
                                onClick={installApp}
                                disabled={installed}
                                className="inline-flex items-center justify-center gap-2 rounded-xl border border-[#d6ddd2] px-5 py-3.5 text-sm font-bold text-[#31433c] transition hover:bg-white disabled:cursor-default disabled:opacity-70"
                            >
                                <DownloadIcon />
                                {installed ? 'Sitex is installed' : 'Install Sitex'}
                            </button>
                        </div>
                    </section>
                </main>

                <footer className="bg-[#102b27] text-white">
                    <div className="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-7 text-sm text-white/65 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <a href="/" className="flex items-center gap-2 font-bold text-white">
                            <BrandMark small />
                            sitex
                        </a>
                        <p>Construction management that moves with your team.</p>
                        {canLogin && !auth?.user && (
                            <Link
                                href={route('login')}
                                className="font-semibold text-[#d4ee9b] hover:text-white"
                            >
                                Sign in to Sitex
                            </Link>
                        )}
                    </div>
                </footer>
            </div>

            {installHelpOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-end justify-center bg-[#091712]/65 p-3 sm:items-center sm:p-6"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget) {
                            setInstallHelpOpen(false);
                        }
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="install-dialog-title"
                        className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-7"
                    >
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-[0.16em] text-[#62805a]">
                                    Sitex app
                                </p>
                                <h2
                                    id="install-dialog-title"
                                    className="mt-2 text-xl font-semibold text-[#172522]"
                                >
                                    Add Sitex to this device
                                </h2>
                            </div>
                            <button
                                type="button"
                                onClick={() => setInstallHelpOpen(false)}
                                aria-label="Close install instructions"
                                className="rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900"
                            >
                                <CloseIcon />
                            </button>
                        </div>
                        {isAppleMobile ? (
                            <ol className="mt-5 space-y-3 text-sm leading-6 text-[#53615a]">
                                <li>
                                    <span className="mr-2 font-bold text-[#36573e]">
                                        1.
                                    </span>
                                    Open this page in Safari.
                                </li>
                                <li>
                                    <span className="mr-2 font-bold text-[#36573e]">
                                        2.
                                    </span>
                                    Tap the Share button in the browser toolbar.
                                </li>
                                <li>
                                    <span className="mr-2 font-bold text-[#36573e]">
                                        3.
                                    </span>
                                    Choose “Add to Home Screen”, then tap Add.
                                </li>
                            </ol>
                        ) : (
                            <ol className="mt-5 space-y-3 text-sm leading-6 text-[#53615a]">
                                <li>
                                    <span className="mr-2 font-bold text-[#36573e]">
                                        1.
                                    </span>
                                    Open this site in Chrome or Edge.
                                </li>
                                <li>
                                    <span className="mr-2 font-bold text-[#36573e]">
                                        2.
                                    </span>
                                    Use the install icon in the address bar, or
                                    open the browser menu.
                                </li>
                                <li>
                                    <span className="mr-2 font-bold text-[#36573e]">
                                        3.
                                    </span>
                                    Choose “Install Sitex” or “Install app”.
                                </li>
                            </ol>
                        )}
                        <p className="mt-5 rounded-xl bg-[#f1f4ed] p-3 text-xs leading-5 text-[#64716b]">
                            If you do not see an install option, open Sitex over
                            HTTPS and try a supported browser.
                        </p>
                    </section>
                </div>
            )}
        </>
    );
}

function FeatureCard({ number, title, text, icon }) {
    return (
        <article className="rounded-2xl border border-[#e6e9e1] bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between">
                <span className="grid h-11 w-11 place-items-center rounded-xl bg-[#edf2e6] text-[#456546]">
                    {icon}
                </span>
                <span className="text-xs font-bold tracking-widest text-[#a1aaa0]">
                    {number}
                </span>
            </div>
            <h3 className="mt-5 text-lg font-semibold text-[#172522]">{title}</h3>
            <p className="mt-2 text-sm leading-6 text-[#64716b]">{text}</p>
        </article>
    );
}

function BrandMark({ small = false }) {
    return (
        <svg
            aria-hidden="true"
            className={small ? 'h-7 w-7' : 'h-9 w-9'}
            viewBox="0 0 40 40"
            fill="none"
        >
            <rect width="40" height="40" rx="12" fill="#D4EE9B" />
            <path
                d="M9 28V15.5L20 9l11 6.5V28M14 28V19h12v9M19 28v-5h3v5"
                stroke="#173327"
                strokeWidth="2.4"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <path d="M7 31h26" stroke="#173327" strokeWidth="2.4" strokeLinecap="round" />
        </svg>
    );
}

function DownloadIcon() {
    return (
        <svg aria-hidden="true" viewBox="0 0 20 20" className="h-4 w-4" fill="none">
            <path
                d="M10 2.75v9m0 0 3.25-3.25M10 11.75 6.75 8.5M3.5 13.5v2A1.75 1.75 0 0 0 5.25 17.25h9.5A1.75 1.75 0 0 0 16.5 15.5v-2"
                stroke="currentColor"
                strokeWidth="1.7"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function ArrowIcon({ direction = 'right' }) {
    return (
        <svg
            aria-hidden="true"
            viewBox="0 0 20 20"
            className={`h-4 w-4 ${direction === 'left' ? 'rotate-180' : ''}`}
            fill="none"
        >
            <path
                d="M3.5 10h12m0 0-4.5-4.5M15.5 10 11 14.5"
                stroke="currentColor"
                strokeWidth="1.7"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function CheckIcon() {
    return (
        <svg aria-hidden="true" viewBox="0 0 20 20" className="h-4 w-4 text-[#d4ee9b]" fill="none">
            <path
                d="m4.5 10.5 3.25 3.25 7.75-7.5"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function CloseIcon() {
    return (
        <svg aria-hidden="true" viewBox="0 0 20 20" className="h-5 w-5" fill="none">
            <path
                d="m5 5 10 10M15 5 5 15"
                stroke="currentColor"
                strokeWidth="1.7"
                strokeLinecap="round"
            />
        </svg>
    );
}

function BlueprintIcon() {
    return (
        <svg aria-hidden="true" viewBox="0 0 24 24" className="h-6 w-6" fill="none">
            <path
                d="M4 20V6l8-3 8 3v14M4 10h16M9 20v-5h6v5M8 7h.01M12 7h.01M16 7h.01"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function MaterialsIcon() {
    return (
        <svg aria-hidden="true" viewBox="0 0 24 24" className="h-6 w-6" fill="none">
            <path
                d="m3 8 9-5 9 5-9 5-9-5Zm0 0v9l9 5 9-5V8m-9 5v9"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function BudgetIcon() {
    return (
        <svg aria-hidden="true" viewBox="0 0 24 24" className="h-6 w-6" fill="none">
            <path
                d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6M16 7h4v4"
                stroke="currentColor"
                strokeWidth="1.7"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function FieldIcon() {
    return (
        <svg aria-hidden="true" viewBox="0 0 24 24" className="h-6 w-6" fill="none">
            <path
                d="M4 20h16M6 20V9l6-5 6 5v11M9 20v-6h6v6M9 9h.01M15 9h.01"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function SiteIllustration({ className = '', compact = false, variant = 0 }) {
    const instanceId = useId().replaceAll(':', '');
    const skyId = `site-sky-${instanceId}`;
    const towerId = `site-tower-${instanceId}`;
    const palettes = [
        ['#e8efe4', '#b6c99d', '#526f57', '#d89a4d'],
        ['#e6edf0', '#a5c0bd', '#51706b', '#cb9453'],
        ['#f0e9dc', '#cdbb91', '#72714f', '#b8764b'],
    ];
    const [sky, mid, dark, accent] = palettes[variant % palettes.length];

    return (
        <svg
            aria-hidden="true"
            viewBox="0 0 720 470"
            className={className}
            role="presentation"
        >
            <defs>
                <linearGradient id={skyId} x1="0" y1="0" x2="1" y2="1">
                    <stop stopColor={sky} />
                    <stop offset="1" stopColor="#f8f4e9" />
                </linearGradient>
                <linearGradient id={towerId} x1="0" y1="0" x2="0" y2="1">
                    <stop stopColor="#f2c978" />
                    <stop offset="1" stopColor={accent} />
                </linearGradient>
            </defs>
            <rect width="720" height="470" rx={compact ? 0 : 34} fill={`url(#${skyId})`} />
            <circle cx="555" cy="100" r="46" fill="#fff5d6" opacity=".8" />
            <path
                d="M0 290 110 225l82 40 109-89 93 92 91-48 95 53 81-42 59 37v117H0V290Z"
                fill={mid}
                opacity=".55"
            />
            <path d="M0 349c124-42 239-41 363 0s237 39 357-4v125H0V349Z" fill="#d4d8bd" />
            <path d="M0 385c122-31 242-25 363 6s239 28 357-2v81H0v-85Z" fill="#788a62" />

            <g opacity=".86" fill="#7e9781">
                <path d="M91 236h46v115H91zM144 208h58v143h-58zM211 247h43v104h-43z" />
                <path d="M98 250h9v13h-9zm20 0h9v13h-9zm-20 25h9v13h-9zm20 0h9v13h-9zm36-23h11v16h-11zm24 0h11v16h-11zm-24 29h11v16h-11zm24 0h11v16h-11z" fill="#e8efe4" />
            </g>

            <g>
                <path d="M280 350V161h202v189H280Z" fill={dark} />
                <path d="M294 176h174v174H294z" fill="#c8d2bf" />
                <path d="M294 176h174v35H294z" fill={`url(#${towerId})`} />
                <path d="M294 217h174v32H294z" fill="#b9c9b0" />
                <path d="M294 255h174v32H294z" fill="#a5b99f" />
                <path d="M294 293h174v57H294z" fill="#91a68d" />
                {[308, 342, 376, 410, 444].map((x) => (
                    <g key={x} fill="#eff2df">
                        <rect x={x} y="184" width="16" height="20" rx="2" />
                        <rect x={x} y="223" width="16" height="19" rx="2" />
                        <rect x={x} y="261" width="16" height="19" rx="2" />
                        <rect x={x} y="301" width="16" height="25" rx="2" />
                    </g>
                ))}
                <path d="M269 161h224l-25-24H294l-25 24Z" fill="#3e5548" />
                <path d="M348 136V66h7v70" fill={accent} />
                <path d="M351 69h171" stroke={accent} strokeWidth="5" strokeLinecap="round" />
                <path d="m514 69-19-12v24l19-12Z" fill={accent} />
                <path d="M351 70 309 104" stroke={accent} strokeWidth="3" />
                <path d="M518 71v66" stroke="#71816f" strokeWidth="2" strokeDasharray="5 5" />
                <path d="M507 137h23l-5-12h-13l-5 12Z" fill="#bf8150" />
                <path d="M344 66h15v-8h-15z" fill="#43594b" />
            </g>

            <g>
                <path d="M466 350V264l63-39 63 39v86H466Z" fill="#e5d2ac" />
                <path d="M466 264h126v23H466z" fill="#c18a4f" />
                <path d="M484 291h26v24h-26zm51 0h26v24h-26zm-25 43h26v16h-26z" fill="#80977e" />
                <path d="m455 266 74-48 74 48" stroke="#755b3e" strokeWidth="8" strokeLinejoin="round" />
                <path d="M616 350V291h55v59" fill="#c0c9ae" />
                <path d="M616 291h55v12h-55z" fill="#72866b" />
                <path d="M626 311h11v14h-11zm22 0h11v14h-11z" fill="#f5f1d8" />
            </g>

            <g fill="#e9c581">
                <path d="m163 351 38-42 38 42h-76Z" />
                <path d="M174 351v-34h54v34" fill="#c78b4c" />
                <path d="M181 327h12v13h-12zm27 0h12v13h-12z" fill="#f2e7c9" />
            </g>
            <path d="M0 355h720" stroke="#52684d" strokeWidth="5" />

            {!compact && (
                <g>
                    <rect x="42" y="57" width="150" height="58" rx="14" fill="white" opacity=".9" />
                    <circle cx="68" cy="86" r="12" fill="#e8efdf" />
                    <path d="m62 86 4 4 8-9" stroke="#456546" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                    <path d="M91 78h77M91 92h52" stroke="#819082" strokeWidth="5" strokeLinecap="round" />
                    <rect x="523" y="383" width="145" height="46" rx="13" fill="#f8f5eb" opacity=".96" />
                    <path d="M543 406h13m-6.5-6.5v13" stroke="#bd8951" strokeWidth="2.5" strokeLinecap="round" />
                    <path d="M570 400h78M570 412h56" stroke="#91a18b" strokeWidth="4" strokeLinecap="round" />
                </g>
            )}
        </svg>
    );
}
