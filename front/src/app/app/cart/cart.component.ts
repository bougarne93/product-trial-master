import { Component, OnInit } from '@angular/core';
import { CartService } from './cart.service';
import { Product } from '../products/data-access/product.model';

@Component({
  selector: 'app-cart',
  templateUrl: './cart.component.html'
})
export class CartComponent implements OnInit {
  items: Product[] = [];

  constructor(private cartService: CartService) {}

  ngOnInit(): void {
    this.items = this.cartService.getItems();
  }

  removeFromCart(productId: number) {
    this.cartService.removeFromCart(productId);
    this.items = this.cartService.getItems();
  }
}
