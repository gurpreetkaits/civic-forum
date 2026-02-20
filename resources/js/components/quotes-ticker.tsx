import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';

const quotes = [
    { text: "The greatness of a nation can be judged by how it treats its weakest member.", author: "Mahatma Gandhi" },
    { text: "Democracy is not just the right to vote, it is the right to live in dignity.", author: "Naomi Klein" },
    { text: "Be the change that you wish to see in the world.", author: "Mahatma Gandhi" },
    { text: "Injustice anywhere is a threat to justice everywhere.", author: "Martin Luther King Jr." },
    { text: "The best argument against democracy is a five-minute conversation with the average voter.", author: "Winston Churchill" },
    { text: "In a gentle way, you can shake the world.", author: "Mahatma Gandhi" },
    { text: "A nation's culture resides in the hearts and in the soul of its people.", author: "Mahatma Gandhi" },
    { text: "Freedom is not worth having if it does not include the freedom to make mistakes.", author: "Mahatma Gandhi" },
    { text: "The only thing necessary for the triumph of evil is for good men to do nothing.", author: "Edmund Burke" },
    { text: "An eye for an eye only ends up making the whole world blind.", author: "Mahatma Gandhi" },
    { text: "Where the mind is without fear and the head is held high — into that heaven of freedom, my Father, let my country awake.", author: "Rabindranath Tagore" },
    { text: "You must not lose faith in humanity. Humanity is an ocean; if a few drops of the ocean are dirty, the ocean does not become dirty.", author: "Mahatma Gandhi" },
    { text: "The measure of a society is found in how they treat their weakest and most helpless citizens.", author: "Jimmy Carter" },
    { text: "Our lives begin to end the day we become silent about things that matter.", author: "Martin Luther King Jr." },
    { text: "Citizenship is not a spectator sport.", author: "Unknown" },
    { text: "The world is a dangerous place, not because of those who do evil, but because of those who look on and do nothing.", author: "Albert Einstein" },
    { text: "Real swaraj will come not by the acquisition of authority by a few but by the acquisition of the capacity by all to resist authority when abused.", author: "Mahatma Gandhi" },
    { text: "A small body of determined spirits fired by an unquenchable faith in their mission can alter the course of history.", author: "Mahatma Gandhi" },
    { text: "The day the power of love overrules the love of power, the world will know peace.", author: "Mahatma Gandhi" },
    { text: "Education is the most powerful weapon which you can use to change the world.", author: "Nelson Mandela" },
];

export default function QuotesTicker() {
    const { i18n } = useTranslation();
    const [currentIndex, setCurrentIndex] = useState(0);
    const [fade, setFade] = useState(true);

    useEffect(() => {
        const interval = setInterval(() => {
            setFade(false);
            setTimeout(() => {
                setCurrentIndex((prev) => (prev + 1) % quotes.length);
                setFade(true);
            }, 600);
        }, 8000);

        return () => clearInterval(interval);
    }, []);

    const quote = quotes[currentIndex];

    return (
        <div className="w-full bg-muted/40 border-b">
            <div className="mx-auto max-w-4xl px-4 py-3 sm:px-6 lg:px-8">
                <div
                    className={`text-center transition-opacity duration-600 ${fade ? 'opacity-100' : 'opacity-0'}`}
                >
                    <p className="text-sm italic text-muted-foreground sm:text-base">
                        "{quote.text}"
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground/70">
                        — {quote.author}
                    </p>
                </div>
            </div>
        </div>
    );
}
