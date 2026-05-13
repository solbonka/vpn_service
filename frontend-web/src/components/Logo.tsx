import { LOGO_SRC, SITE_NAME } from '../constants/branding';

type LogoProps = {
  size?: 'sm' | 'md' | 'lg' | 'xl';
  showLabel?: boolean;
  className?: string;
};

const sizeClass = {
  sm: 'logo-img--sm',
  md: 'logo-img--md',
  lg: 'logo-img--lg',
  xl: 'logo-img--xl',
};

export default function Logo({ size = 'md', showLabel = true, className = '' }: LogoProps) {
  const dims =
    size === 'xl' ? 140 : size === 'lg' ? 96 : size === 'sm' ? 36 : 44;

  return (
    <span className={`logo-wrap ${className}`.trim()}>
      <img
        src={LOGO_SRC}
        alt={SITE_NAME}
        className={`logo-img ${sizeClass[size]}`}
        width={dims}
        height={dims}
        decoding="async"
      />
      {showLabel && <span className="logo-label">{SITE_NAME}</span>}
    </span>
  );
}
