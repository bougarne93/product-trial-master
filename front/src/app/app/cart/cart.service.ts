import { Injectable } from '@angular/core';
import { Product } from '../products/data-access/product.model';

@Injectable({ providedIn: 'root' })
export class CartService {
  private items: Product[] = [];

  addToCart(product: Product) {
    this.items.push(product);
  }

  removeFromCart(productId: number) {
    this.items = this.items.filter(p => p.id !== productId);
  }

  getItems() {
    return this.items;
  }

  clearCart() {
    this.items = [];
  }
}
