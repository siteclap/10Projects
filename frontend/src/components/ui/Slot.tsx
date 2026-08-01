import {
  forwardRef,
  isValidElement,
  cloneElement,
  type ReactNode,
  type HTMLAttributes,
  type Ref,
} from 'react';
import { cn } from '@/lib/utils/cn';

export interface SlotProps extends HTMLAttributes<HTMLElement> {
  children: ReactNode;
}

export const Slot = forwardRef<HTMLElement, SlotProps>(function Slot(
  { children, className, ...props },
  ref
) {
  if (!isValidElement(children)) {
    return null;
  }

  const childProps = children.props as Record<string, unknown>;

  return cloneElement(children, {
    ...props,
    ...childProps,
    ref,
    className: cn(className, childProps.className as string | undefined),
  } as Record<string, unknown>);
});
